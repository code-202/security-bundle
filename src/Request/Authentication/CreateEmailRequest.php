<?php

declare(strict_types=1);

namespace Code202\Security\Request\Authentication;

use Code202\Security\Request\ServiceRequest;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateEmailRequest implements ServiceRequest
{
    #[Email]
    #[NotBlank]
    public string $email = '';
}
