<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Form\AddressFormType;
use App\Form\EditUserFormType;
use App\Form\SecurityCentreType;
use App\Repository\AddressRepository;
use App\Service\AddressService;
use App\Service\UserService;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use function Symfony\Component\String\u;

#[Route(path: '/user')]
class UserController extends AbstractController
{
    private UserService $userService;
    private EntityManagerInterface $em;
    private AddressService $addressService;
    private AddressRepository $addressRepository;

    public function __construct(UserService $userService, EntityManagerInterface $em, AddressRepository $addressRepository, AddressService $addressService){
        $this->userService = $userService;
        $this->em = $em;
        $this->addressService = $addressService;
        $this->addressRepository = $addressRepository;
    }

    #[Route(path: '/login', name: 'user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/register', name: 'user_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterFormType::class, $user);

        $form->handleRequest($request);

        if($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {

            return $this->redirectToRoute('user_login');
        }
        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/me', name: 'user_profile')]
    public function profile(Request $request): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(EditUserFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->editUser($user, $form);
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/me/security-centre', name: 'user_security_centre')]
    public function securityCentre(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedHttpException('You must be logged in to access this page.');
        }

        $form = $this->createForm(SecurityCentreType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();

            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'You have successfully amended your details.');
            return $this->redirectToRoute('user_security_centre');
        }

        return $this->render('user/securityCentre.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/deactive', name: 'user_deactivate')]
    public function deactivate(Request $request): Response
    {
        $user = $this->getUser();
        $this->userService->deleteUser($user);
        return $this->redirectToRoute('user_logout');
    }

    #[Route('/me/get-address/{id}', name: 'get_address', methods: ['GET'])]
    public function getAddress(int $id): JsonResponse
    {
        $address = $this->em->getRepository(Address::class)->find($id);

        if(!$address){
            return new JsonResponse(['message' => 'Address not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'line' => $address->getLine(),
            'city' => $address->getCity(),
            'country' => $address->getCountry(),
            'postcode' => $address->getPostcode(),
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
        $address = $this->em->getRepository(Address::class)->find($id);

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
    public function deleteAddress(Request $request, int $id): Response
    {
        $address = $this->em->getRepository(Address::class)->find($id);

        if (!$address) {
            throw $this->createNotFoundException('Address not found');
        }

        $this->addressService->removeAddress($address);
        $this->addFlash('success', 'Address deleted successfully.');
        return $this->redirectToRoute('user_addresses');
    }

    #[Route('/me/orders', name: 'user_orders', methods: ['GET'])]
    public function viewMyOrders(): Response
    {
        $user = $this->getUser();

        $orders = $user->getOrders();

        return $this->render('user/user_orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route(path: '/logout', name: 'user_logout')]
    public function logout(): void
    {
    }
}
