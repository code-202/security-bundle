<?php

namespace Code202\Security\Exception;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class FormValidationFailedException extends ValidationFailedException implements ExceptionInterface
{
    public function __construct(
        FormInterface $form
    ) {
        $list = new ConstraintViolationList();

        foreach ($form->getErrors(true, true) as $error) {
            $cause = $error->getCause();
            if ($cause instanceof ConstraintViolation) {
                $list->add($cause);
            }
        }

        parent::__construct(null, $list);
    }
}
