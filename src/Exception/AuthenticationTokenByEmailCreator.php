<?php

declare(strict_types=1);

namespace Code202\Security\Exception;

use RuntimeException;

class AuthenticationTokenByEmailCreator extends RuntimeException implements ExceptionInterface {}
