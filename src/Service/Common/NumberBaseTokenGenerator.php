<?php

declare(strict_types=1);

namespace Code202\Security\Service\Common;

class NumberBaseTokenGenerator implements TokenGeneratorInterface
{
    public function __construct(
        protected int $size
    ) {}

    public function generate(): string
    {
        $max = 10 ** $this->size - 1;

        return str_pad((string) random_int(0, $max), $this->size, '0');
    }
}
