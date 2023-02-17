<?php

namespace Code202\Security\Authenticator\Trait;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Code202\Security\Authenticator\Passport\Badge\PermanentSessionBadge;
use Code202\Security\Authenticator\Passport\Badge\VerifyAuthenticationBadge;
use Code202\Security\Entity\AuthenticationType;

trait UsernamePasswordAuthenticatorTrait
{
    protected function buildPassport(array $credentials): Passport
    {
        $passport = new Passport(
            new UserBadge($credentials['key'], function ($key) {
                try {
                    return $this->userProvider->loadUserByIdentifier(AuthenticationType::USERNAME_PASSWORD->value.':'.$key);
                } catch (UserNotFoundException $e) {
                }

                // trying to basicly get user (for incompitable user provider)
                return $this->userProvider->loadUserByIdentifier($key);
            }),
            new PasswordCredentials($credentials['password']),
            [new VerifyAuthenticationBadge(), new RememberMeBadge()]
        );

        if ($credentials['remember_me']) {
            $passport->addBadge(new PermanentSessionBadge());
        }

        return $passport;
    }
}
