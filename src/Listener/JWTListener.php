<?php

declare(strict_types=1);

namespace Code202\Security\Listener;

use Code202\Security\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created', method: 'onJWTCreated')]
class JWTListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        $payload = $event->getData();

        $payload['roles'] = $user->getRoles();

        if ($user instanceof UserInterface) {
            $ttl = 2678400; // 60 * 60 * 24 * 31;
            $payload['exp'] = time() + $ttl;

            $payload['authentication'] = $user->getAuthentication()->getType();
            $payload['name'] = $user->getAccount()->getName();
        }

        $event->setData($payload);
    }
}
