<?php

namespace Code202\Security\Bridge\Ramsey\Uuid;

use Code202\Security\Uuid\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid;

class UuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::uuid4();
    }
}
