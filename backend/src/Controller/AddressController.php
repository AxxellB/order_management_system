<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressFormType;
use App\Repository\AddressRepository;
use App\Service\AddressService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

class AddressController extends AbstractController
{
    private AddressService $addressService;
    private AddressRepository $addressRepository;

    public function __construct(AddressRepository $addressRepository, AddressService $addressService){
        $this->addressService = $addressService;
        $this->addressRepository = $addressRepository;
    }
    #[Route('/me/addresses', name: 'user_addresses')]
    public function index(): Response
    {
        $user = $this->getUser();

        $addresses = $user->getAddresses();
        return $this->render('address/index.html.twig', [
            'addresses' => $addresses,
        ]);
    }

    #[Route('/me/create-address', name: 'create_address', methods: ['GET', 'POST'])]
    public function createAddress(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedHttpException('You must be logged in to access this page.');
        }

        $address = new Address();
        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addressService->addAddress($user, $address);

            $this->addFlash('success', 'You have successfully created a new address.');
            return $this->redirectToRoute('user_addresses');
        }

        return $this->render('address/createAddress.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/me/edit-address/{id}', name: 'edit_address', methods: ['GET', 'POST'])]
    public function editAddress(Request $request, int $id): Response
    {
        $address = $this->addressRepository->find($id);

        if (!$address) {
            throw $this->createNotFoundException('Address not found');
        }

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addressService->editAddress($address);

            $this->addFlash('success', 'Address updated successfully.');
            return $this->redirectToRoute('user_addresses');
        }

        return $this->render('address/editAddress.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/me/delete-address/{id}', name: 'delete_address', methods: ['POST'])]
    public function deleteAddress(int $id): Response
    {
        $address = $this->addressRepository->find($id);

        if (!$address) {
            throw $this->createNotFoundException('Address not found');
        }

        $this->addressService->removeAddress($address);
        $this->addFlash('success', 'Address deleted successfully.');
        return $this->redirectToRoute('user_addresses');
    }
}
