<?php

declare(strict_types=1);

namespace Code202\Security\Request\Account;

use Code202\Security\Request\ServiceRequest;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateNameRequest implements ServiceRequest
{
    #[NotBlank]
    public string $name = '';
}
