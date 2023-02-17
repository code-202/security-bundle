<?php

namespace Code202\Security\Service\Common;

class NumberBaseTokenGenerator implements TokenGeneratorInterface
{
    protected int $size;

    public function __construct(int $size)
    {
        $this->size = $size;
    }

    public function generate(): string
    {
        $max = pow(10, $this->size) - 1;

        return str_pad(rand(0, $max), $this->size, '0');
    }
}
