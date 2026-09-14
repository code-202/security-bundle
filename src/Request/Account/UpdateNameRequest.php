<?php

declare(strict_types=1);

namespace Code202\Security\Request\Account;

use Symfony\Component\Validator\Constraints\NotBlank;
use Code202\Security\Request\ServiceRequest;

class UpdateNameRequest implements ServiceRequest
{
    #[NotBlank]
    public string $name = '';
}
