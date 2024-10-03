<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (method_exists($user, 'getDeletedAt') && $user->getDeletedAt() !== null) {
            throw new CustomUserMessageAccountStatusException('Your account has been deactivated.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
    }
}
