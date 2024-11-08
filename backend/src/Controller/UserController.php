<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Form\EditUserFormType;
use App\Form\SecurityCentreType;
use App\Repository\UserRepository;
use App\Service\TwoFactorAuthenticatorService;
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
    private TwoFactorAuthenticatorService $twoFactorAuthenticatorService;

    public function __construct(UserService $userService, EntityManagerInterface $em, UserRepository $userRepository, TwoFactorAuthenticatorService  $twoFactorAuthenticatorService)
    {
        $this->userService = $userService;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->twoFactorAuthenticatorService = $twoFactorAuthenticatorService;
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
            $isAdmin = $user->isAdmin();

            if ($isAdmin) {
                $this->twoFactorAuthenticatorService->generateAndSend2FACode($user->getId(), $user->getEmail());

                return new JsonResponse([
                    'message' => 'Login successful! 2FA code sent.',
                    'userId' => $user->getId(),
                    'token' => $token,
                    'isAdmin' => $isAdmin,
                    'requires2FA' => true
                ], Response::HTTP_OK);
            }

            return new JsonResponse([
                'message' => 'Login successful!',
                'token' => $token,
                'isAdmin' => $isAdmin,
                'requires2FA' => false
            ], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => $loginResult['message']], $loginResult['status_code']);
    }


    #[Route(path: '/api/verify-2fa', name: 'user_api_verify_2fa', methods: ['POST'])]
    public function verify2FA(Request $request, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['user_id']) || !isset($data['code'])) {
            return new JsonResponse(['message' => 'Missing required parameters.'], Response::HTTP_BAD_REQUEST);
        }

        $userId = $data['user_id'];
        $submittedCode = $data['code'];

        $storedCode = $this->twoFactorAuthenticatorService->get2FACode($userId);

        if ($storedCode !== null && $storedCode === (int)$submittedCode) {
            $this->twoFactorAuthenticatorService->invalidate2FACode($userId);

            $user = $this->userService->getUserById($userId);
            if (!$user) {
                return new JsonResponse(['message' => 'Ooops, something went wrong.'], JsonResponse::HTTP_NOT_FOUND);
            }

            $token = $jwtManager->create($user);

            return new JsonResponse(['message' => '2FA verification successful!', 'token' => $token], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Invalid or expired 2FA code.'], Response::HTTP_FORBIDDEN);
    }

    #[Route(path: '/api/resend-2fa', name: 'user_api_resend_2fa', methods: ['POST'])]
    public function resend2FA(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['user_id'])) {
            return new JsonResponse(['message' => 'Missing required parameters.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $userId = $data['user_id'];


        $user = $this->userService->getUserById($userId);
        if (!$user) {
            return new JsonResponse(['message' => 'User not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        try {
            $this->twoFactorAuthenticatorService->regenerateAndSend2FACode($userId, $user->getEmail());
            return new JsonResponse(['message' => 'A new 2FA code has been sent to your email.'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Failed to resend 2FA code.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/register', name: 'user_api_register', methods: ['POST'])]
    public function apiRegister(Request $request, UserService $userService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $registrationResult = $userService->register($data);

        if ($registrationResult['status_code'] == Response::HTTP_CREATED) {
            return new JsonResponse(['user' => $registrationResult['user_id']], $registrationResult['status_code']);
        }

        return new JsonResponse(['errors' => $registrationResult['errors']], $registrationResult['status_code']);
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
