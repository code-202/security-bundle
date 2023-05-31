<?php

namespace Code202\Security\Request\Authentication;

use Symfony\Component\Validator\Constraints\NotBlank;
use Code202\Security\Request\ServiceRequest;

class UpdatePasswordRequest implements ServiceRequest
{
    #[NotBlank]
    public string $new = '';
}
