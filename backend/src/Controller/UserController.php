<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Form\EditUserFormType;
use App\Form\SecurityCentreType;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserController extends AbstractController
{
    private UserService $userService;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    public function __construct(UserService $userService, EntityManagerInterface $em, UserRepository $userRepository){
        $this->userService = $userService;
        $this->em = $em;
        $this->userRepository = $userRepository;
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

    #[Route(path: '/api/login', name: 'user_api_login')]
    public function apiLogin(Request $request, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $loginResult = $this->userService->login($data['email'], $data['password']);

        if ($loginResult['success']) {
            /** @var UserInterface $user */
            $user = $loginResult['user'];
            $token = $jwtManager->create($user);

            return new JsonResponse([
                'message' => 'Login successful!',
                'token' => $token
            ], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => $loginResult['message']], $loginResult['status_code']);
    }


    #[Route(path: '/api/register', name: 'user_api_register', methods: ['POST'])]
    public function apiRegister(Request $request, UserService $userService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $registrationResult = $userService->register($data);

        if ($registrationResult['success']) {
            return new JsonResponse(['message' => 'User created!'], Response::HTTP_CREATED);
        }

        return new JsonResponse(['message' => $registrationResult['message']], $registrationResult['status_code']);
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
