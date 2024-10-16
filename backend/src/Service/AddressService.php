<?php
namespace App\Service;
use App\Entity\Address;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AddressService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    public function createAddress(User $user, array $data): Address
    {
        $address = new Address();
        $address->setLine($data['line']);
        $address->setLine2($data['line2'] ?? null);
        $address->setCity($data['city']);
        $address->setCountry($data['country']);
        $address->setPostcode($data['postcode']);

        $user->addAddress($address);
        $this->em->persist($address);
        $this->em->persist($user);
        $this->em->flush();

        return $address;
    }

    public function updateAddress(array $data, Address $address)
    {
        $address->setLine($data['line']);
        $address->setLine2($data['line2'] ?? null);
        $address->setCity($data['city']);
        $address->setCountry($data['country']);
        $address->setPostcode($data['postcode']);

        $this->em->persist($address);
        $this->em->flush();
    }

    public function deleteAddress(Address $address)
    {
        $this->em->remove($address);
        $this->em->flush();
    }

    private function formatValidationErrors(ConstraintViolationListInterface $errors): array
    {
        $formattedErrors = [];

        foreach ($errors as $error) {
            $field = str_replace(['[', ']'], '', $error->getPropertyPath());
            $formattedErrors[$field] = $error->getMessage();
        }

        return $formattedErrors;
    }

    public function validateAddressData(array $data): array
    {
        $constraints = new Assert\Collection([
            'line' => new Assert\NotBlank(['message' => 'Line is required.']),
            'line2' => new Assert\Optional(),
            'city' => new Assert\NotBlank(['message' => 'City is required.']),
            'country' => new Assert\NotBlank(['message' => 'Country is required.']),
            'postcode' => new Assert\NotBlank(['message' => 'Postal code is required.']),
        ]);

        $errors = $this->validator->validate($data, $constraints);

        return $this->formatValidationErrors($errors);
    }
}
