<?php

namespace Code202\Security\Entity\Activity;

enum Type: string
{
    case ACCOUNT_CREATED = 'security.account_created';
    case ACCOUNT_ENABLED = 'security.account_enabled';
    case ACCOUNT_DISABLED = 'security.account_disabled';
    case ACCOUNT_NAME_CHANGED = 'security.account_name_changed';
    case ROLE_GRANTED = 'security.role_granted';
    case ROLE_REVOKED = 'security.role_revoked';
    case AUTHENTICATION_CREATED = 'security.authentication_created';
    case LOGIN = 'security.login';
    case LOGOUT = 'security.logout';
    case SESSION_DELETED = 'security.session_deleted';
    case SESSION_TRUSTED = 'security.session_trusted';
    case SESSION_UNTRUSTED = 'security.session_untrusted';
    case PASSWORD_CHANGED = 'security.password_changed';
    case USERNAME_CHANGED = 'security.username_changed';
    case TOKEN_BY_EMAIL_REFRESHED = 'security.token_by_email_refreshed';
    case TOKEN_BY_EMAIL_VERIFIED = 'security.token_by_email_verified';
    case EMAIL_CHANGED = 'security.email_changed';
}
