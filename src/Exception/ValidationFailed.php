<?php

namespace Code202\Security\Exception;

use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationFailed extends ValidationFailedException implements ExceptionInterface
{
}
