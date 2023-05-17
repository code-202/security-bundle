<?php

namespace Code202\Security\Service\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\TokenByEmailVerifiedEvent;
use Code202\Security\Exception;
use Code202\Security\Exception\AuthenticationTokenByEmailVerifier;
use Code202\Security\User\User;

class TokenByEmailVerifier
{
    public function __construct(
        private EntityManagerInterface $em,
        private PasswordHasherFactoryInterface $passwordHasherFactory,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function verify(Authentication|string $authenticationOrUuid, string $token, bool $autoFlush = true): string
    {
        if ($authenticationOrUuid instanceof Authentication) {
            $authentication = $authenticationOrUuid;
        } else {
            $authentication = $this->em->getRepository(Authentication::class)->findOneBy([
                'uuid' => $authenticationOrUuid,
            ]);
        }

        if (!$authentication || !$authentication->isEnabled()) {
            throw new Exception\AuthenticationTokenByEmailVerifier('authentication_not_found');
        }

        if ($authentication->getType() != AuthenticationType::TOKEN_BY_EMAIL) {
            throw new Exception\AuthenticationTokenByEmailVerifier('authentication_is_not_token_by_email_type');
        }

        if ($authentication->isVerified()) {
            throw new Exception\AuthenticationTokenByEmailVerifier('authentication_already_verified');
        }

        if (!$authentication->getData('password')) {
            throw new Exception\AuthenticationTokenByEmailVerifier('authentication_has_no_validation_token');
        }

        $now = new \DatetimeImmutable();

        if ($authentication->getData('expired_at')) {
            if ($now > new \Datetime($authentication->getData('expired_at'))) {
                throw new Exception\AuthenticationTokenByEmailVerifier('too_late');
            }
        }

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(User::class);

        if (!$passwordHasher->verify($authentication->getData('password'), $token)) {
            throw new Exception\AuthenticationTokenByEmailVerifier('token_not_match');
        }

        $authentication
            ->setVerified(true)
            ->setData('password', '')
        ;

        $this->em->persist($authentication);

        $event = new TokenByEmailVerifiedEvent($authentication, ['email' => $authentication->getKey(), 'token' => $token]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }

        return $token;
    }
}
