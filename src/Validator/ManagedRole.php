<?php

namespace Code202\Security\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ManagedRole extends Constraint
{
    public string $message = 'The role "{{ role }}" is not managed by the security role strategies manager.';
}
