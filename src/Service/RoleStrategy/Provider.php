<?php

namespace Code202\Security\Service\RoleStrategy;

use Symfony\Component\ExpressionLanguage\Expression;

class Provider implements ProviderInterface
{
    protected array $strategies;

    protected Collection $collection;

    public function __construct(
        array $strategies = []
    ) {
        $this->collection = new Collection();

        foreach ($strategies as $strategy) {
            $this->collection->add(new BasicRoleStrategy(
                $strategy['roles'],
                new Expression($strategy['to_grant']),
                new Expression($strategy['to_revoke'] ?? $strategy['to_grant'])
            ));
        }
    }

    public function getStrategiesFor(string $role): Collection
    {
        $collection = new Collection();

        foreach ($this->collection as $strategy) {
            if ($strategy->hasRole($role)) {
                $collection->add($strategy);
            }
        }

        return $collection;
    }

    public function getIterator(): \Traversable
    {
        return $this->collection;
    }
}
