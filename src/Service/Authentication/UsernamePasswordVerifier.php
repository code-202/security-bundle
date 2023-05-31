<?php

namespace Code202\Security\Service\Authentication;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Exception;
use Code202\Security\User\User;

class UsernamePasswordVerifier
{
    public function __construct(
        private PasswordHasherFactoryInterface $hasherFactory,
    ) {
    }

    public function verify(
        Authentication $authentication,
        string $password = '',
    ): bool {
        if (!$authentication) {
            throw new Exception\AuthenticationUsernamePasswordVerifier('authentication_not_found');
        }

        if ($authentication->getType() != AuthenticationType::USERNAME_PASSWORD) {
            throw new Exception\AuthenticationUsernamePasswordVerifier('authentication_is_not_username_password_type');
        }

        $passwordHasher = $this->hasherFactory->getPasswordHasher(User::class);

        if (!$password) {
            throw new Exception\AuthenticationUsernamePasswordVerifier('password_empty');
        }

        return $passwordHasher->verify($authentication->getData('password'), $password);
    }
}
