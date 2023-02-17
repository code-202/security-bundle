<?php

namespace Code202\Security\Service\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Event\Authentication\TokenByEmailRefreshedEvent;
use Code202\Security\Exception;
use Code202\Security\Exception\AuthenticationTokenByEmailRefresher;
use Code202\Security\Service\Common\TokenGeneratorInterface;
use Code202\Security\User\User;

class TokenByEmailRefresher
{
    protected EntityManagerInterface $em;
    protected PasswordHasherFactoryInterface $passwordHasherFactory;
    protected TokenGeneratorInterface $tokenGenerator;
    protected string $minimalRefreshInterval;
    protected string $lifetimeInterval;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        PasswordHasherFactoryInterface $passwordHasherFactory,
        TokenGeneratorInterface $tokenGenerator,
        $minimalRefreshInterval,
        $lifetimeInterval,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->passwordHasherFactory = $passwordHasherFactory;
        $this->tokenGenerator = $tokenGenerator;
        $this->minimalRefreshInterval = $minimalRefreshInterval;
        $this->lifetimeInterval = $lifetimeInterval;
        $this->eventDispatcher = $eventDispatcher;
    }

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
            throw new Exception\AuthenticationTokenByEmailRefresher('authentication_not_found');
        }

        if ($authentication->getType() != AuthenticationType::TOKEN_BY_EMAIL) {
            throw new Exception\AuthenticationTokenByEmailRefresher('authentication_is_not_token_by_email_type');
        }

        $now = new \DatetimeImmutable();
        $limitGeneratedAt = $now->modify('-'.$this->minimalRefreshInterval);

        if ($authentication->getData('generated_at')) {
            $generatedAt = new \Datetime($authentication->getData('generated_at'));

            if ($generatedAt > $limitGeneratedAt) {
                throw new Exception\AuthenticationTokenByEmailRefresher('too_soon');
            }
        }

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(User::class);

        $token = $this->tokenGenerator->generate();

        $passwordEncoded = $passwordHasher->hash($token);

        $authentication
            ->setData('password', $passwordEncoded)
            ->setData('generated_at', $now->format('Y-m-d H:i:s'))
            ->setData('expired_at', $now->modify('+'.$this->lifetimeInterval)->format('Y-m-d H:i:s'))
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
