<?php

namespace App\Controller;
use App\Entity\Address;
use App\Repository\AddressRepository;
use App\Service\AddressService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class AddressController extends AbstractController
{
    private AddressService $addressService;
    private AddressRepository $addressRepository;

    public function __construct(AddressRepository $addressRepository, AddressService $addressService)
    {
        $this->addressService = $addressService;
        $this->addressRepository = $addressRepository;
    }

    #[Route('/addresses', name: 'api_addresses', methods: ['GET'])]
    public function apiGetAddresses(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $addresses = array_filter($user->getAddresses()->toArray(), function ($address) {
            return $address->getOrderEntity() === null;
        });

        if (!$addresses) {
            return new JsonResponse(['message' => 'User addresses not found'], Response::HTTP_NOT_FOUND);
        }

        $addressData = array_map([$this, 'formatAddress'], $addresses);

        $addressData = array_values($addressData);
        return new JsonResponse(['addresses' => $addressData]);
    }

    #[Route('/address/{id}', name: 'api_get_address', methods: ['GET'])]
    public function apiGetAddress(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $address = $this->addressRepository->find($id);

        if (!$address) {
            return new JsonResponse(['message' => 'Address not found'], Response::HTTP_NOT_FOUND);
        }

        $addressData = $this->formatAddress($address);

        return new JsonResponse(['address' => $addressData]);
    }

    #[Route('/addresses', name: 'api_create_address', methods: ['POST'])]
    public function apiCreateAddress(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $errors = $this->addressService->validateAddressData($data);

        if (count($errors) > 0) {
            return new JsonResponse([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $newAddress = $this->addressService->createAddress($user, $data);

        return new JsonResponse([
            $this->formatAddress($newAddress)
        ], Response::HTTP_CREATED);
    }


    #[Route('/address/{id}', name: 'api_edit_address', methods: ['PUT'])]
    public function apiEditAddress(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $address = $this->addressRepository->find($id);

        if (!$address) {
            return new JsonResponse(['message' => 'Address not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $errors = $this->addressService->validateAddressData($data);

        if (count($errors) > 0) {
            return new JsonResponse([
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->addressService->updateAddress($data, $address);

        return new JsonResponse([
            $this->formatAddress($address)
        ], Response::HTTP_OK);
    }

    #[Route('/address/{id}', name: 'api_delete_address', methods: ['DELETE'])]
    public function apiDeleteAddress(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $address = $this->addressRepository->find($id);

        if (!$address) {
            return new JsonResponse(['message' => 'Address not found'], Response::HTTP_NOT_FOUND);
        }

        $this->addressService->deleteAddress($address);

        return new JsonResponse(['message' => 'Address deleted successfully!'], Response::HTTP_NO_CONTENT);
    }

    public function formatAddress(Address $address): array
    {
        return [
            'id' => $address->getId(),
            'line' => $address->getLine(),
            'line2' => $address->getLine2() ?? '',
            'city' => $address->getCity(),
            'country' => $address->getCountry(),
            'postcode' => $address->getPostCode(),
        ];
    }
}
