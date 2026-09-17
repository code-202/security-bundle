<?php

declare(strict_types=1);

namespace Code202\Security\Uuid;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
