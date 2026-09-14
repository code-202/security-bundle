<?php

declare(strict_types=1);

namespace Code202\Security\Service\RoleStrategy;

use Symfony\Component\ExpressionLanguage\Expression;

class BasicRoleStrategy implements RoleStrategyInterface
{
    protected array $roles;

    protected Expression|string $conditionsToGrant;

    protected Expression|string $conditionsToRevoke;

    public function __construct(
        array $roles,
        Expression|string $conditionsToGrant,
        Expression|string $conditionsToRevoke
    ) {
        $this->roles = $roles;
        $this->conditionsToGrant = $conditionsToGrant;
        $this->conditionsToRevoke = $conditionsToRevoke;
    }

    public function getConditionsToGrant(): Expression|string
    {
        return $this->conditionsToGrant;
    }

    public function getConditionsToRevoke(): Expression|string
    {
        return $this->conditionsToRevoke;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }
}
