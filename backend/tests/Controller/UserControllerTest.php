<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testApiRegister(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/user/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@example.com',
                'password' => 'Password123',
                'confirmPassword' => 'Password123',
            ])
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('User created!', $responseContent['message']);
    }

    public function testApiLogin(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/user/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'john.doe@example.com',
                'password' => 'Password123',
            ])
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Login successful!', $responseContent['message']);

        $this->assertArrayHasKey('token', $responseContent);
        $this->assertNotEmpty($responseContent['token']);

        $tokenParts = explode('.', $responseContent['token']);
        $this->assertCount(3, $tokenParts);
    }
}
