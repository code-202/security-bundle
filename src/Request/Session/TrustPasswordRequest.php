<?php

declare(strict_types=1);

namespace Code202\Security\Request\Session;

use Code202\Security\Request\ServiceRequest;
use Symfony\Component\Validator\Constraints\NotBlank;

class TrustPasswordRequest implements ServiceRequest
{
    #[NotBlank]
    public string $password = '';
}
