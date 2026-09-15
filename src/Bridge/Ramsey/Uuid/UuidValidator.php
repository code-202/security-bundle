<?php

declare(strict_types=1);

namespace Code202\Security\Bridge\Ramsey\Uuid;

use Code202\Security\Uuid\UuidValidatorInterface;
use Ramsey\Uuid\Uuid;

class UuidValidator implements UuidValidatorInterface
{
    public function validate(string $uuid): bool
    {
        return Uuid::isValid($uuid);
    }
}
