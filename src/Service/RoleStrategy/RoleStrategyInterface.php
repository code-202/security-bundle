<?php

declare(strict_types=1);

namespace Code202\Security\Service\RoleStrategy;

use Symfony\Component\ExpressionLanguage\Expression;

interface RoleStrategyInterface
{
    public function getConditionsToGrant(): Expression|string;

    public function getConditionsToRevoke(): Expression|string;

    /** @return array<string> */
    public function getRoles(): array;

    public function hasRole(string $role): bool;
}
