<?php

namespace Code202\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Code202\Security\Service\RoleStrategy\Manager as RoleStrategiesManager;
use Code202\Security\User\UserInterface;

class RoleManipulationVoter extends Voter
{
    public const GRANT = 'SECURITY.ROLE.GRANT';
    public const REVOKE = 'SECURITY.ROLE.REVOKE';

    protected RoleStrategiesManager $manager;

    public function __construct(
        RoleStrategiesManager $manager
    ) {
        $this->manager = $manager;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [
            self::GRANT,
            self::REVOKE,
        ])) {
            return false;
        }

        if (!is_string($subject)) {
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
            self::GRANT => $this->manager->canGrant($subject),
            self::REVOKE => $this->manager->canRevoke($subject),
            default => throw new \LogicException('This code should not be reached!')
        };
    }
}
