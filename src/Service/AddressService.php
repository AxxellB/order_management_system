<?php
namespace App\Service;
use App\Entity\Address;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AddressService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addAddress(User $user, Address $address)
    {
        $address->setUser($user);

        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }
}
