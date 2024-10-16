<?php

namespace App\Controller;
use App\Repository\AddressRepository;
use App\Service\AddressService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/')]
class AddressController extends AbstractController
{
    private AddressService $addressService;
    private AddressRepository $addressRepository;

    public function __construct(AddressRepository $addressRepository, AddressService $addressService){
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
            return $address->getOrderEntity() == null;
        });

        if(!$addresses){
            return new JsonResponse([
                'message' => 'User addresses not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $addressData = array_map(function ($address) {
            return [
                'id' => $address->getId(),
                'line' => $address->getLine(),
                'line2' => $address->getLine2() ?? "",
                'city' => $address->getCity(),
                'postal_code' => $address->getPostCode(),
            ];
        }, $addresses);

        $addressData = array_values($addressData);

        return new JsonResponse([
            'addresses' => $addressData
        ]);
    }

    #[Route('/address/{id}', name: 'api_get_address', methods: ['GET'])]
    public function apiGetAddress(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $address = $this->addressRepository->find($id);

        if(!$address){
            return new JsonResponse([
                'message' => 'Address not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $addressData = [
                'id' => $address->getId(),
                'line' => $address->getLine(),
                'line2' => $address->getLine2() ?? "",
                'city' => $address->getCity(),
                'postal_code' => $address->getPostCode(),
            ];

        return new JsonResponse([
            'address' => $addressData
        ]);
    }

    #[Route('/addresses', name: 'api_create_address', methods: ['POST'])]
    public function apiCreateAddress(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $addressResult = $this->addressService->addAddress($user, $data);

        return new JsonResponse([
            $addressResult
        ]);
    }

    #[Route('/address/{id}', name: 'api_edit_address', methods: ['PUT'])]
    public function apiEditAddress(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $address = $this->addressRepository->find($id);

        if(!$address){
            return new JsonResponse([
                'message' => 'Address not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $addressResult = $this->addressService->editAddress($data, $address);

        return new JsonResponse([
            $addressResult
        ]);
    }

    #[Route('/address/{id}', name: 'api_delete_address', methods: ['DELETE'])]
    public function apiDeleteAddress(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page.'], Response::HTTP_UNAUTHORIZED);
        }

        $address = $this->addressRepository->find($id);

        if(!$address){
            return new JsonResponse([
                'message' => 'Address not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->addressService->deleteAddress($address);

        return new JsonResponse([
            'message' => 'Address deleted successfully!'
        ], Response::HTTP_NO_CONTENT);
    }

//    #[Route('/me/addresses', name: 'user_addresses')]
//    public function index(): Response
//    {
//        $user = $this->getUser();
//
//        $addresses = array_filter($user->getAddresses()->toArray(), function ($address) {
//            return $address->getOrderEntity() == null;
//        });
//
//        return $this->render('address/index.html.twig', [
//            'addresses' => $addresses,
//        ]);
//    }

//    #[Route('/me/create-address', name: 'create_address', methods: ['GET', 'POST'])]
//    public function createAddress(Request $request): Response
//    {
//        $user = $this->getUser();
//
//        if (!$user) {
//            throw new AccessDeniedHttpException('You must be logged in to access this page.');
//        }
//
//        $address = new Address();
//        $form = $this->createForm(AddressFormType::class, $address);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $this->addressService->addAddress($user, $address);
//
//            $this->addFlash('success', 'You have successfully created a new address.');
//            return $this->redirectToRoute('user_addresses');
//        }
//
//        return $this->render('address/createAddress.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }
//    #[Route('/me/edit-address/{id}', name: 'edit_address', methods: ['GET', 'POST'])]
//    public function editAddress(Request $request, int $id): Response
//    {
//        $address = $this->addressRepository->find($id);
//
//        if (!$address) {
//            throw $this->createNotFoundException('Address not found');
//        }
//
//        $form = $this->createForm(AddressFormType::class, $address);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $this->addressService->editAddress($address);
//
//            $this->addFlash('success', 'Address updated successfully.');
//            return $this->redirectToRoute('user_addresses');
//        }
//
//        return $this->render('address/editAddress.html.twig', [
//            'form' => $form->createView(),
//        ]);
//    }
//
//    #[Route('/me/delete-address/{id}', name: 'delete_address', methods: ['POST'])]
//    public function deleteAddress(int $id): Response
//    {
//        $address = $this->addressRepository->find($id);
//
//        if (!$address) {
//            throw $this->createNotFoundException('Address not found');
//        }
//
//        $this->addressService->removeAddress($address);
//        $this->addFlash('success', 'Address deleted successfully.');
//        return $this->redirectToRoute('user_addresses');
//    }
}
