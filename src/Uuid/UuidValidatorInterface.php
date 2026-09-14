<?php

declare(strict_types=1);

namespace Code202\Security\Uuid;

interface UuidValidatorInterface
{
    public function validate(string $uuid): bool;
}
