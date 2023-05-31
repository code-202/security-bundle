<?php

namespace Code202\Security\Request\Authentication;

use Symfony\Component\Validator\Constraints\NotBlank;
use Code202\Security\Request\ServiceRequest;

class UpdateUsernameRequest implements ServiceRequest
{
    #[NotBlank]
    public string $username = '';
}
