<?php

namespace Code202\Security\Service\RoleStrategy;

interface ProviderInterface extends \IteratorAggregate
{
    public function getStrategiesFor(string $role): Collection;
}
