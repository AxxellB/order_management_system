<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function createUser($data): User
    {
        $user = new User();
        $user->setFirstName($data->getFirstName());
        $user->setLastName($data->getLastName());
        $user->setEmail($data->getEmail());
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data->getPassword());
        $user->setPassword($hashedPassword);

        $address = new Address();
        $address->setLine($data->getAddresses()[0]->getLine());
        $address->setCity($data->getAddresses()[0]->getCity());
        $address->setCountry($data->getAddresses()[0]->getCountry());
        $address->setPostcode($data->getAddresses()[0]->getPostCode());

        $user->addAddress($address);
        $address->setUser($user);

        $this->entityManager->persist($address);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function editUser(User $user, FormInterface $form): void
    {
        $selectedAddress = $form->get('selectAddress')->getData();
        if($selectedAddress) {
            $addressFormData = $form->get('addressDetails')->getData();

            $selectedAddress->setLine($addressFormData->getLine());
            $selectedAddress->setCity($addressFormData->getCity());
            $selectedAddress->setCountry($addressFormData->getCountry());
            $selectedAddress->setPostcode($addressFormData->getPostcode());
        }

        $plainPassword = $form->get('password')->getData();
        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

        $this->entityManager->persist($selectedAddress);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function deleteUser(User $user): void
    {
        $user = $this->userRepository->find($user->getId());
        if(!$user){
            throw new \Exception("User not found");
        }
        $user->setDeletedAt(new \DateTimeImmutable());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
