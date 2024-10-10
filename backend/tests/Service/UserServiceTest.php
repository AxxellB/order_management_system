<?php
namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserServiceTest extends KernelTestCase
{
    private UserService $userService;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->userService = self::getContainer()->get(UserService::class);
        $this->userRepository = self::getContainer()->get(UserRepository::class);
    }

    public function testRegister(): void
    {
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe2@example.com',
            'password' => 'Password123',
            'confirmPassword' => 'Password123',
        ];

        $result = $this->userService->register($data);

        $this->assertTrue($result['success']);
        $this->assertEquals('User created!', $result['message']);

    }

    public function testRegisterWithDuplicateEmail(): void
    {
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe2@example.com',
            'password' => 'Password123',
            'confirmPassword' => 'Password123',
        ];

        $result = $this->userService->register($data);

        $this->assertFalse($result['success']);
        $this->assertEquals('A user with that email already exists.', $result['message']);

    }

    public function testLogin(): void
    {
        $data = [
            'email' => 'john.doe@example.com',
            'password' => 'Password123',
        ];

        $result = $this->userService->login($data['email'], $data['password']);

        $this->assertTrue($result['success']);
        $this->assertInstanceOf(User::class, $result['user']);
    }

    public function testLoginWithWrongCredentials(): void
    {
        $data = [
            'email' => 'wrong.email@example.com',
            'password' => 'wrongpassword',
        ];

        $result = $this->userService->login($data['email'], $data['password']);

        $this->assertFalse($result['success']);
        $this->assertEquals('Wrong email or password.', $result['message']);
    }
}
