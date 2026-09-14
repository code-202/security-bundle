<?php

declare(strict_types=1);

namespace Code202\Security\Service\RoleStrategy;

interface ProviderInterface extends \IteratorAggregate
{
    public function getStrategiesFor(string $role): Collection;
}
