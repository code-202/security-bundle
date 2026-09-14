<?php

namespace Code202\Security\Service\Authentication;

use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\TokenByEmailVerifiedEvent;
use Code202\Security\Exception\AuthenticationTokenByEmailVerifier;
use Code202\Security\User\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TokenByEmailVerifier
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

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
            throw new AuthenticationTokenByEmailVerifier('authentication_not_found');
        }

        if (AuthenticationType::TOKEN_BY_EMAIL != $authentication->getType()) {
            throw new AuthenticationTokenByEmailVerifier('authentication_is_not_token_by_email_type');
        }

        if ($authentication->isVerified()) {
            throw new AuthenticationTokenByEmailVerifier('authentication_already_verified');
        }

        if (!$authentication->getData('password')) {
            throw new AuthenticationTokenByEmailVerifier('authentication_has_no_validation_token');
        }

        $now = new DateTimeImmutable();

        if ($authentication->getData('expired_at')) {
            if ($now > new DateTime($authentication->getData('expired_at'))) {
                throw new AuthenticationTokenByEmailVerifier('too_late');
            }
        }

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(User::class);

        if (!$passwordHasher->verify($authentication->getData('password'), $token)) {
            throw new AuthenticationTokenByEmailVerifier('token_not_match');
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
