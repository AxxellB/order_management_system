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

    public function editAddress(array $data, Address $address)
    {
        $constraints = new Assert\Collection([
            'line' => new Assert\NotBlank(['message' => 'Line is required.']),
            'line2' => new Assert\Optional(),
            'city' => new Assert\NotBlank(['message' => 'City is required.']),
            'country' => new Assert\NotBlank(['message' => 'Country is required.']),
            'postcode' => new Assert\NotBlank(['message' => 'Postal code is required.']),
        ]);

        $errors = $this->validator->validate($data, $constraints);

        if (count($errors) > 0) {
            return [
                'status' => 'error',
                'errors' => $this->formatValidationErrors($errors)
            ];
        }

        $address->setLine($data['line']);
        $address->setLine2($data['line2'] ?? null);
        $address->setCity($data['city']);
        $address->setCountry($data['country']);
        $address->setPostcode($data['postcode']);

        $this->em->persist($address);
        $this->em->flush();

        return [
            'status' => 'success',
            'address' => $address,
        ];
    }

    public function deleteAddress(Address $address)
    {
        $this->em->remove($address);
        $this->em->flush();
    }
}
