<?php

namespace Code202\Security\Entity\Activity;

enum Type: string
{
    case ACCOUNT_CREATED = 'account_created';
    case ACCOUNT_ENABLED = 'account_enabled';
    case ACCOUNT_DISABLED = 'account_disabled';
    case ACCOUNT_NAME_CHANGED = 'account_name_changed';
    case ROLE_GRANTED = 'role_granted';
    case ROLE_REVOKED = 'role_revoked';
    case AUTHENTICATION_CREATED = 'authentication_created';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case SESSION_DELETED = 'session_deleted';
    case SESSION_TRUSTED = 'session_trusted';
    case SESSION_UNTRUSTED = 'session_untrusted';
    case PASSWORD_CHANGED = 'password_changed';
    case USERNAME_CHANGED = 'username_changed';
    case TOKEN_BY_EMAIL_REFRESHED = 'token_by_email_refreshed';
    case TOKEN_BY_EMAIL_VERIFIED = 'token_by_email_verified';
    case EMAIL_CHANGED = 'email_changed';
}
