<?php

namespace Code202\Security\Bridge\Ramsey\Uuid;

use Ramsey\Uuid\Uuid;
use Code202\Security\Uuid\UuidValidatorInterface;

class UuidValidator implements UuidValidatorInterface
{
    public function validate(string $uuid): bool
    {
        return Uuid::isValid($uuid);
    }
}
