<?php

namespace Code202\Security\Bridge\Ramsey\Uuid;

use Ramsey\Uuid\Uuid;
use Code202\Security\Uuid\UuidGeneratorInterface;

class UuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::uuid4();
    }
}
