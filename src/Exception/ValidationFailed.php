<?php

declare(strict_types=1);

namespace Code202\Security\Exception;

use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationFailed extends ValidationFailedException implements ExceptionInterface {}
