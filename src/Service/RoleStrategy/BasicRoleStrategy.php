<?php

namespace Code202\Security\Service\RoleStrategy;

use Symfony\Component\ExpressionLanguage\Expression;

class BasicRoleStrategy implements RoleStrategyInterface
{
    protected array $roles;

    protected string|Expression $conditionsToGrant;

    protected string|Expression $conditionsToRevoke;

    public function __construct(
        array $roles,
        string|Expression $conditionsToGrant,
        string|Expression $conditionsToRevoke
    ) {
        $this->roles = $roles;
        $this->conditionsToGrant = $conditionsToGrant;
        $this->conditionsToRevoke = $conditionsToRevoke;
    }

    public function getConditionsToGrant(): string|Expression
    {
        return $this->conditionsToGrant;
    }

    public function getConditionsToRevoke(): string|Expression
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
