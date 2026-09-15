<?php

namespace Code202\Security\Voter;

use Code202\Security\Entity\Session;
use Code202\Security\User\UserInterface;
use DateTime;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SessionVoter extends Voter
{
    public const DELETE = 'SECURITY.SESSION.DELETE';
    public const TRUST = 'SECURITY.SESSION.TRUST';
    public const UNTRUST = 'SECURITY.SESSION.UNTRUST';
    public const TRUSTED = 'SECURITY.SESSION.TRUSTED';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (self::TRUSTED === $attribute) {
            return true;
        }

        if (!in_array($attribute, [
            self::DELETE,
            self::TRUST,
            self::UNTRUST,
        ])) {
            return false;
        }
        return $subject instanceof Session;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
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
            self::TRUSTED => null != $user->getSession()->getTrustUntil() && $user->getSession()->getTrustUntil() > new DateTime('now'),
            default => throw new LogicException('This code should not be reached!')
        };
    }
}
