<?php

namespace Code202\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Code202\Security\Entity\Authentication;
use Code202\Security\User\UserInterface;

class AuthenticationVoter extends Voter
{
    public const EDIT = 'SECURITY.AUTHENTICATION.EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Authentication) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return match ($attribute) {
            self::EDIT => $subject->getAccount() == $user->getAccount(),
            default => throw new \LogicException('This code should not be reached!')
        };
    }
}
