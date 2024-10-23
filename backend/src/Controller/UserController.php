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

    public function __construct(UserService $userService, EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->userService = $userService;
        $this->em = $em;
        $this->userRepository = $userRepository;
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

    #[Route(path: '/api/user-profile', name: 'api_user_profile', methods: ['GET'])]
    public function viewUserProfile(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page!'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
        ], Response::HTTP_OK);
    }

    #[Route(path: '/api/change-password', name: 'api_user_change_password', methods: ['PUT'])]
    public function changePassword(Request $request, UserService $userService, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'You must be logged in to access this page!'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $oldPassword = $data['oldPassword'];
        $newPassword = $data['newPassword'];
        $confirmPassword = $data['confirmPassword'];

        $result = $userService->changePassword($user, $oldPassword, $newPassword, $confirmPassword, $passwordHasher);

        return new JsonResponse(['message' => $result['message']], $result['statusCode']);
    }
}
