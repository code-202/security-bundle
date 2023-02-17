<?php

namespace Code202\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Code202\Security\Entity\Session;
use Code202\Security\User\UserInterface;

class SessionVoter extends Voter
{
    public const DELETE = 'SECURITY.SESSION.DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Session) {
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
            self::DELETE => $subject->getAuthentication()->getAccount() == $user->getAccount(),
            default => throw new \LogicException('This code should not be reached!')
        };
    }
}
