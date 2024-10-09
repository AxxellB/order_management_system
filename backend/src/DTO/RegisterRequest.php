<?php
namespace App\DTO;

use App\Validator\Constraints\MatchPassword;
use App\Validator\Constraints\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequest
{
    #[Assert\NotBlank(message: "First name is required.")]
    public string $firstName;

    #[Assert\NotBlank(message: "Last name is required.")]
    public string $lastName;

    #[Assert\NotBlank(message: "Email is required.")]
    #[UniqueEmail]
    #[Assert\Email(message: "Invalid email format.")]
    public string $email;

    #[Assert\NotBlank(message: "Password is required.")]
    #[Assert\Length(min: 8, minMessage: "Password must be at least {{ limit }} characters long.")]
    #[Assert\Regex(
        pattern: "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/",
        message: "Password must include both letters and numbers."
    )]
    public string $password;

    #[Assert\NotBlank(message: "Confirm password is required.")]
    #[MatchPassword]
    public string $confirmPassword;
}

