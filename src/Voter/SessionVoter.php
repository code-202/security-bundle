<?php

namespace Code202\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Code202\Security\Entity\Session;
use Code202\Security\User\UserInterface;

class SessionVoter extends Voter
{
    public const DELETE = 'SECURITY.SESSION.DELETE';
    public const TRUST = 'SECURITY.SESSION.TRUST';
    public const UNTRUST = 'SECURITY.SESSION.UNTRUST';
    public const TRUSTED = 'SECURITY.SESSION.TRUSTED';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute == self::TRUSTED) {
            return true;
        }

        if (!in_array($attribute, [
            self::DELETE,
            self::TRUST,
            self::UNTRUST,
        ])) {
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
            self::TRUST,
            self::UNTRUST,
            self::DELETE => $subject->getAuthentication()->getAccount() == $user->getAccount(),
            self::TRUSTED => $user->getSession()->getTrustUntil() != null && $user->getSession()->getTrustUntil() > new \DateTime('now'),
            default => throw new \LogicException('This code should not be reached!')
        };
    }
}
