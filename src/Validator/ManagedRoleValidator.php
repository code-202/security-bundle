<?php

declare(strict_types=1);

namespace Code202\Security\Validator;

use Code202\Security\Service\RoleStrategy\Provider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

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

        if (0 == count($collection)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ role }}', $value)
                ->addViolation()
            ;
        }
    }
}
