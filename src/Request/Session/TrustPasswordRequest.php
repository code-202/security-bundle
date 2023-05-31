<?php

namespace Code202\Security\Request\Session;

use Symfony\Component\Validator\Constraints\NotBlank;
use Code202\Security\Request\ServiceRequest;

class TrustPasswordRequest implements ServiceRequest
{
    #[NotBlank]
    public string $password = '';
}
