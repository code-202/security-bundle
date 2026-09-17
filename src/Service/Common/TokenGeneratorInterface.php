<?php

declare(strict_types=1);

namespace Code202\Security\Service\Common;

interface TokenGeneratorInterface
{
    public function generate(): string;
}
