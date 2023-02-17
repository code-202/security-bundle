<?php

namespace Code202\Security\Bridge\Symfony\Polyfill\Uuid;

use Code202\Security\Uuid\UuidGeneratorInterface;

class UuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return uuid_create(UUID_TYPE_RANDOM);
    }
}
