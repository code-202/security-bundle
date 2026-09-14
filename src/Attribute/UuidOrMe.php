<?php

declare(strict_types=1);

namespace Code202\Security\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class UuidOrMe
{
    public function __construct(
        public string $name = 'uuid',
    ) {
    }
}
