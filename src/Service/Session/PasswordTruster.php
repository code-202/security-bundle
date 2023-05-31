<?php

namespace Code202\Security\Service\Session;

use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Entity\Session;
use Code202\Security\Exception\SessionTrust;
use Code202\Security\Service\Authentication\Provider as AuthenticationProvider;
use Code202\Security\Service\Authentication\UsernamePasswordVerifier;

class PasswordTruster
{
    public function __construct(
        private Truster $truster,
        private AuthenticationProvider $authenticationProvider,
        private UsernamePasswordVerifier $usernamePasswordVerifier,
    ) {
    }

    public function trust(Session $session, string $password, bool $autoFlush = true)
    {
        $authentication = $session->getAuthentication();

        if ($authentication->getType() != AuthenticationType::USERNAME_PASSWORD) {
            $authentication = $this->authenticationProvider->getOne($authentication->getAccount(), AuthenticationType::USERNAME_PASSWORD);
            if (!$authentication) {
                throw new SessionTrust('no_username_password_authentication');
            }
        }

        if (!$this->usernamePasswordVerifier->verify($authentication, $password)) {
            throw new SessionTrust('password_does_not_match');
        }

        $this->truster->trust($session, $autoFlush);
    }
}
