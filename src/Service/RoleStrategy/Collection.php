<?php

declare(strict_types=1);

namespace Code202\Security\Service\RoleStrategy;

use Ramsey\Collection\AbstractCollection;

/** @extends AbstractCollection<RoleStrategyInterface> */
class Collection extends AbstractCollection
{
    public function getType(): string
    {
        return RoleStrategyInterface::class;
    }
}
