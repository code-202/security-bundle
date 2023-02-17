<?php

namespace Code202\Security\Bridge\Symfony\Polyfill\Uuid;

use Code202\Security\Uuid\UuidValidatorInterface;

class UuidValidator implements UuidValidatorInterface
{
    public function validate(string $uuid): bool
    {
        return uuid_is_valid($uuid);
    }
}
