<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LoginRequest
{
    #[Assert\NotBlank(message: 'Email cannot be empty.')]
    #[Assert\Email(message: 'Invalid email format.')]
    public string $email;

    #[Assert\NotBlank(message: 'Password cannot be empty.')]
    public string $password;
}
