<?php

declare(strict_types=1);

namespace Code202\Security\Entity;

enum AuthenticationType: string
{
    case USERNAME_PASSWORD = 'username_password';
    case TOKEN_BY_EMAIL = 'token_by_email';
    case ANONYMOUS = 'anonymous';
}
