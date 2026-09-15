<?php

declare(strict_types=1);

namespace Code202\Security\Service\RoleStrategy;

use Symfony\Component\ExpressionLanguage\Expression;

class BasicRoleStrategy implements RoleStrategyInterface
{
    /** @param array<string> $roles */
    public function __construct(
        protected array $roles,
        protected Expression|string $conditionsToGrant,
        protected Expression|string $conditionsToRevoke
    ) {}

    public function getConditionsToGrant(): Expression|string
    {
        return $this->conditionsToGrant;
    }

    public function getConditionsToRevoke(): Expression|string
    {
        return $this->conditionsToRevoke;
    }

    /** @return array<string> */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }
}
