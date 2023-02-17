<?php

namespace Code202\Security\Service\RoleStrategy;

use Symfony\Bundle\SecurityBundle\Security;

class Manager
{
    protected ProviderInterface $provider;
    protected Security $security;

    public function __construct(
        ProviderInterface $provider,
        Security $security
    ) {
        $this->provider = $provider;
        $this->security = $security;
    }

    public function canGrant(string $role): bool
    {
        foreach ($this->provider->getStrategiesFor($role) as $strategy) {
            if ($this->security->isGranted($strategy->getConditionsToGrant())) {
                return true;
            }
        }

        return false;
    }

    public function canRevoke(string $role): bool
    {
        foreach ($this->provider->getStrategiesFor($role) as $strategy) {
            if ($this->security->isGranted($strategy->getConditionsToRevoke())) {
                return true;
            }
        }

        return false;
    }

    public function getGrantableRoles(): array
    {
        $grantableRoles = [];

        foreach ($this->provider as $strategy) {
            if ($this->security->isGranted($strategy->getConditionsToGrant())) {
                $grantableRoles = array_merge($grantableRoles, $strategy->getRoles());
            }
        }

        return array_unique($grantableRoles);
    }

    public function getRevocableRoles(): array
    {
        $revokableRoles = [];

        foreach ($this->provider as $strategy) {
            if ($this->security->isGranted($strategy->getConditionsToRevoke())) {
                $revokableRoles = array_merge($revokableRoles, $strategy->getRoles());
            }
        }

        return array_unique($revokableRoles);
    }
}
