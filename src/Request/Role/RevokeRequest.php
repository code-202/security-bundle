<?php

declare(strict_types=1);

namespace Code202\Security\Request\Role;

use Code202\Security\Entity\Account;
use Code202\Security\Request\ServiceRequest;
use Code202\Security\Validator\ManagedRole;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class RevokeRequest implements ServiceRequest
{
    #[NotBlank]
    #[ManagedRole]
    public string $role = '';

    #[NotNull]
    #[OA\Property(type: 'string')]
    public Account $account;
}
