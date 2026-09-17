<?php

declare(strict_types=1);

namespace Code202\Security\Exception;

use RuntimeException;

class AuthenticationUsernamePasswordVerifier extends RuntimeException implements ExceptionInterface {}
