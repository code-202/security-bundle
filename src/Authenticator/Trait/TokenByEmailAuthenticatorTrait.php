<?php

namespace Code202\Security\Authenticator\Trait;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Code202\Security\Authenticator\Passport\Badge\ResetPasswordAuthenticationBadge;
use Code202\Security\Authenticator\Passport\Badge\TrustSessionBadge;
use Code202\Security\Authenticator\Passport\Badge\VerifyAuthenticationBadge;
use Code202\Security\Entity\AuthenticationType;

trait TokenByEmailAuthenticatorTrait
{
    protected function buildPassport(array $credentials): Passport
    {
        $passport = new Passport(
            new UserBadge(AuthenticationType::TOKEN_BY_EMAIL->value.':'.$credentials['key'], $this->userProvider->loadUserByIdentifier(...)),
            new PasswordCredentials($credentials['password']),
            [new VerifyAuthenticationBadge(), new ResetPasswordAuthenticationBadge(), new TrustSessionBadge()]
        );

        return $passport;
    }
}
