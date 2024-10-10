<?php

namespace App\Service;

use App\DTO\LoginRequest;
use App\DTO\RegisterRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    public function register(array $data): array
    {
        $registerRequest = new RegisterRequest();
        $registerRequest->firstName = $data['firstName'] ?? '';
        $registerRequest->lastName = $data['lastName'] ?? '';
        $registerRequest->email = $data['email'] ?? '';
        $registerRequest->password = $data['password'] ?? '';
        $registerRequest->confirmPassword = $data['confirmPassword'] ?? '';

        $errors = $this->validator->validate($registerRequest);

        if (count($errors) > 0) {
            return [
                'success' => false,
                'message' => $this->getErrorMessages($errors),
                'status_code' => Response::HTTP_BAD_REQUEST
            ];
        }

        $user = new User();
        $user->setFirstName($registerRequest->firstName);
        $user->setLastName($registerRequest->lastName);
        $user->setEmail($registerRequest->email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $registerRequest->password);
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $this->userRepository->save($user, true);

        return ['success' => true, 'message' => 'User created!', 'status_code' => Response::HTTP_CREATED];
    }

    public function login(string $email, string $password): array
    {
        $loginRequest = new LoginRequest();
        $loginRequest->email = $email;
        $loginRequest->password = $password;

        $errors = $this->validator->validate($loginRequest);

        if (count($errors) > 0) {
            return [
                'success' => false,
                'message' => $this->getErrorMessages($errors),
                'status_code' => Response::HTTP_BAD_REQUEST
            ];
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !password_verify($password, $user->getPassword())) {
            return [
                'success' => false,
                'message' => 'Wrong email or password.',
                'status_code' => Response::HTTP_UNAUTHORIZED
            ];
        }

        return [
            'success' => true,
            'user' => $user,
            'status_code' => Response::HTTP_OK
        ];
    }

    private function getErrorMessages(ConstraintViolationListInterface $violations): string
    {
        $errorMessages = [];

        foreach ($violations as $violation) {
            $errorMessages[] = $violation->getMessage();
        }

        return implode(', ', $errorMessages);
    }

    public function editUser(User $user, FormInterface $form): void
    {

        $plainPassword = $form->get('password')->getData();
        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

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
