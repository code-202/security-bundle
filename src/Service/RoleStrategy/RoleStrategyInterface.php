<?php

namespace Code202\Security\Service\RoleStrategy;

use Symfony\Component\ExpressionLanguage\Expression;

interface RoleStrategyInterface
{
    public function getConditionsToGrant(): string|Expression;
    public function getConditionsToRevoke(): string|Expression;
    public function getRoles(): array;
    public function hasRole(string $role): bool;
}
