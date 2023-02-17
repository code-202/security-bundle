<?php

namespace Code202\Security\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Code202\Security\Service\RoleStrategy\Provider;

class ManagedRoleValidator extends ConstraintValidator
{
    protected Provider $provider;

    public function __construct(
        Provider $provider
    ) {
        $this->provider = $provider;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ManagedRole) {
            throw new UnexpectedTypeException($constraint, ManagedRole::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $collection = $this->provider->getStrategiesFor($value);

        if (count($collection) == 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ role }}', $value)
                ->addViolation();
        }
    }
}
