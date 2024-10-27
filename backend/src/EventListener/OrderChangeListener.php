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
    private array $removals = [];

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

        if ($entity instanceof Order || $entity instanceof OrderProduct || $entity instanceof Address) {
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
        $this->logOrderChanges($event, 'add');
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

        $oldValue = [];
        $newValue = [];

        if ($entity instanceof Order) {
            $orderChanges = $unitOfWork->getEntityChangeSet($entity);

            foreach ($orderChanges as $field => [$old, $new]) {
                $oldValue[$field] = $old;
                $newValue[$field] = $new;
            }

            foreach ($entity->getOrderProducts() as $orderProduct) {
                $productChanges = $unitOfWork->getEntityChangeSet($orderProduct);
                $productName = $orderProduct->getProductEntity()->getName();

                foreach ($productChanges as $field => [$old, $new]) {
                    if ($field === 'quantity') {
                        if (!isset($oldValue['products'])) {
                            $oldValue['products'] = [];
                        }
                        if (!isset($newValue['products'])) {
                            $newValue['products'] = [];
                        }
                        $oldValue['products'][] = [
                            'product' => $productName,
                            'quantity' => $old,
                        ];
                        $newValue['products'][] = [
                            'product' => $productName,
                            'quantity' => $new,
                        ];
                    }
                }
            }
        }

        if ($entity instanceof Address) {
            $addressChanges = $unitOfWork->getEntityChangeSet($entity);

            foreach ($addressChanges as $field => [$old, $new]) {
                $oldValue[$field] = $old;
                $newValue[$field] = $new;
            }
            $action = 'address_update';
        }

        if (!empty($oldValue) || !empty($newValue)) {
            $this->loggerService->logOrderChange(
                $entity instanceof Order ? $entity : $entity->getOrderEntity(),
                $user,
                $action,
                $oldValue,
                $newValue
            );
        }
    }
}
