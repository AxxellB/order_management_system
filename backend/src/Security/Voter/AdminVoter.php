<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminVoter extends Voter
{
    public const ADMIN_ACCESS = 'ADMIN_ACCESS';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::ADMIN_ACCESS;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}


