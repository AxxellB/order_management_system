<?php
namespace App\Service;
use App\Entity\Address;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AddressService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addAddress(User $user, Address $address)
    {
        $user->addAddress($address);
        $this->em->persist($address);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function editAddress(Address $address)
    {
        $this->em->persist($address);
        $this->em->flush();
    }

    public function removeAddress(Address $address)
    {
        $this->em->remove($address);
        $this->em->flush();
    }
}
