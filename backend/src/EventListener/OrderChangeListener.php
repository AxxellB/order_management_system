<?php

namespace App\EventListener;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Service\OrderLoggerService;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class OrderChangeListener implements EventSubscriberInterface
{
    private OrderLoggerService $loggerService;
    private Security $security;

    public function __construct(OrderLoggerService $loggerService, Security $security)
    {
        $this->loggerService = $loggerService;
        $this->security = $security;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postUpdate,
            Events::postPersist,
        ];
    }

    public function postUpdate(PostUpdateEventArgs $event): void
    {
        $entity = $event->getObject();

        if ($entity instanceof Order || $entity instanceof OrderProduct || ($entity instanceof Address && $this->isAddressLinkedToOrder($entity))) {
            $action = 'update';

            $unitOfWork = $event->getObjectManager()->getUnitOfWork();
            $changes = $unitOfWork->getEntityChangeSet($entity);

            if (array_key_exists('deletedAt', $changes)) {
                $oldDeletedAt = $changes['deletedAt'][0];
                $newDeletedAt = $changes['deletedAt'][1];

                if ($oldDeletedAt === null && $newDeletedAt !== null) {
                    $action = 'delete';
                } elseif ($oldDeletedAt !== null && $newDeletedAt === null) {
                    $action = 'restore';
                }
            }

            $this->logOrderChanges($event, $action);
        }
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();

        if ($entity instanceof Order || $entity instanceof OrderProduct || ($entity instanceof Address && $this->isAddressLinkedToOrder($entity))) {
            $this->logOrderChanges($event, 'add');
        }
    }

    private function logOrderChanges(LifecycleEventArgs $event, string $action): void
    {
        $entity = $event->getObject();
        $user = $this->security->getUser();

        if (!$user || !($entity instanceof Order || $entity instanceof OrderProduct || $entity instanceof Address)) {
            return;
        }

        $entityManager = $event->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        if ($entity instanceof Order) {
            $orderChanges = $unitOfWork->getEntityChangeSet($entity);

            foreach ($orderChanges as $field => [$old, $new]) {
                if ($field !== 'totalAmount') {
                    $this->loggerService->logOrderChange(
                        $entity,
                        $user,
                        $action,
                        [$field => $old],
                        [$field => $new]
                    );
                }
            }
        }

        if ($entity instanceof OrderProduct) {
            $productChanges = $unitOfWork->getEntityChangeSet($entity);
            $productName = $entity->getProductEntity()->getName();

            foreach ($productChanges as $field => [$old, $new]) {
                if ($field === 'quantity') {
                    $this->loggerService->logOrderChange(
                        $entity->getOrderEntity(),
                        $user,
                        $action,
                        ['product' => $productName, 'quantity' => $old],
                        ['product' => $productName, 'quantity' => $new]
                    );
                }
            }
        }

        if ($entity instanceof Address && $this->isAddressLinkedToOrder($entity)) {
            $addressChanges = $unitOfWork->getEntityChangeSet($entity);

            foreach ($addressChanges as $field => [$old, $new]) {
                $this->loggerService->logOrderChange(
                    $entity->getOrderEntity(),
                    $user,
                    'address_update',
                    [$field => $old],
                    [$field => $new]
                );
            }
        }
    }

    /**
     *
     *
     * @param Address $address
     * @return bool
     */
    private function isAddressLinkedToOrder(Address $address): bool
    {
        return $address->getOrderEntity() !== null;
    }
}
