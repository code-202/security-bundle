<?php

declare(strict_types=1);

namespace Code202\Security\Service\RoleStrategy;

use IteratorAggregate;

interface ProviderInterface extends IteratorAggregate
{
    public function getStrategiesFor(string $role): Collection;
}
