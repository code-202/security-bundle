<?php

namespace Code202\Security\Service\RoleStrategy;

use Ramsey\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function getType(): string
    {
        return RoleStrategyInterface::class;
    }
}
