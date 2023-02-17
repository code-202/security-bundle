<?php

namespace Code202\Security\Uuid;

interface UuidValidatorInterface
{
    public function validate(string $uuid): bool;
}
