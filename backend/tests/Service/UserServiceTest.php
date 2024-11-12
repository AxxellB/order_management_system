<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserServiceTest extends TestCase
{
    private $entityManager;
    private $userRepository;
    private $passwordHasher;
    private $validator;
    private $userService;

    public function testRegisterWithExistingEmail()
    {
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123'
        ];

        $user = new User();
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $data['email']])
            ->willReturn($user);

        $response = $this->userService->register($data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response['status_code']);
        $this->assertContains('Email already exists', $response['errors']['email'] ?? []);
    }


    public function testRegisterWithPasswordMismatch()
    {
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'confirmPassword' => 'differentPassword'
        ];

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Password confirmation does not match.', '', [], '', 'confirmPassword', '')
        ]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $response = $this->userService->register($data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response['status_code']);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('confirmPassword', $response['errors']);
    }


    public function testLoginWithNonExistentUser()
    {
        $email = 'nonexistent@example.com';
        $password = 'password123';

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(null);

        $response = $this->userService->login($email, $password);

        $this->assertFalse($response['success']);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response['status_code']);
        $this->assertEquals('Wrong email or password.', $response['message']);
    }


    public function testChangePasswordWithEmptyFields()
    {
        $user = new User();

        $response = $this->userService->changePassword($user, '', '', '');

        $this->assertFalse($response['success']);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response['statusCode']);
        $this->assertEquals('All fields are required', $response['message']);
    }


    public function testChangePasswordWithNonMatchingConfirmPassword()
    {
        $user = new User();
        $user->setPassword('hashed_old_password');

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, 'old_password')
            ->willReturn(true);

        $response = $this->userService->changePassword($user, 'old_password', 'new_password', 'different_new_password');

        $this->assertFalse($response['success']);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response['statusCode']);
        $this->assertEquals('Confirm password should match the new one', $response['message']);
    }


    public function testChangePasswordWithWeakPassword()
    {
        $user = new User();
        $user->setPassword('hashed_old_password');

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, 'old_password')
            ->willReturn(true);

        $response = $this->userService->changePassword($user, 'old_password', 'weak', 'weak');

        $this->assertFalse($response['success']);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response['statusCode']);
        $this->assertEquals('Password must contain at least one letter, one number and be at least 8 characters long', $response['message']);
    }


    public function testDeleteUserNonExistent()
    {
        $user = new User();
        $user->setId(999);

        $this->userRepository->expects($this->once())
            ->method('find')
            ->with($user->getId())
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User not found');

        $this->userService->deleteUser($user);
    }


    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->userService = new UserService(
            $this->entityManager,
            $this->userRepository,
            $this->passwordHasher,
            $this->validator
        );
    }
}
