<?php

namespace Code202\Security\Request\Authentication;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Code202\Security\Request\ServiceRequest;

class CreateEmailRequest implements ServiceRequest
{
    #[Email]
    #[NotBlank]
    public string $email = '';
}
