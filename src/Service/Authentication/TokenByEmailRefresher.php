<?php

namespace Code202\Security\Service\Authentication;

use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\TokenByEmailRefreshedEvent;
use Code202\Security\Exception\AuthenticationTokenByEmailRefresher;
use Code202\Security\Service\Common\TokenGeneratorInterface;
use Code202\Security\User\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TokenByEmailRefresher
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected PasswordHasherFactoryInterface $passwordHasherFactory,
        protected TokenGeneratorInterface $tokenGenerator,
        protected string $minimalRefreshInterval,
        protected string $lifetimeInterval,
        protected EventDispatcherInterface $eventDispatcher
    ) {}

    public function refresh(Authentication|string $authenticationOrUuid, bool $autoFlush = true): string
    {
        if ($authenticationOrUuid instanceof Authentication) {
            $authentication = $authenticationOrUuid;
        } else {
            $authentication = $this->em->getRepository(Authentication::class)->findOneBy([
                'uuid' => $authenticationOrUuid,
            ]);
        }

        if (!$authentication || !$authentication->isEnabled()) {
            throw new AuthenticationTokenByEmailRefresher('authentication_not_found');
        }

        if (AuthenticationType::TOKEN_BY_EMAIL != $authentication->getType()) {
            throw new AuthenticationTokenByEmailRefresher('authentication_is_not_token_by_email_type');
        }

        $now = new DateTimeImmutable();
        $limitGeneratedAt = $now->modify('-' . $this->minimalRefreshInterval);

        if ($authentication->getData('generated_at')) {
            $generatedAt = new DateTime($authentication->getData('generated_at'));

            if ($generatedAt > $limitGeneratedAt) {
                throw new AuthenticationTokenByEmailRefresher('too_soon');
            }
        }

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(User::class);

        $token = $this->tokenGenerator->generate();

        $passwordEncoded = $passwordHasher->hash($token);

        $authentication
            ->setData('password', $passwordEncoded)
            ->setData('generated_at', $now->format('Y-m-d H:i:s'))
            ->setData('expired_at', $now->modify('+' . $this->lifetimeInterval)->format('Y-m-d H:i:s'))
        ;

        $this->em->persist($authentication);

        $event = new TokenByEmailRefreshedEvent($authentication, ['email' => $authentication->getKey(), 'token' => $token]);
        $this->eventDispatcher->dispatch($event);

        if ($autoFlush) {
            $this->em->flush();
        }

        return $token;
    }
}
