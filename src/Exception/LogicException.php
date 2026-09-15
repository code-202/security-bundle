<?php

declare(strict_types=1);

namespace Code202\Security\Exception;

use LogicException as GlobalLogicException;

class LogicException extends GlobalLogicException implements ExceptionInterface {}
