<?php

namespace Code202\Security\Service\Common;

interface TokenGeneratorInterface
{
    public function generate(): string;
}
