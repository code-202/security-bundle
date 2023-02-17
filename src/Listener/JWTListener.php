<?php

namespace Code202\Security\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Code202\Security\User\UserInterface;

#[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created', method: 'onJWTCreated')]
class JWTListener
{
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        $payload = $event->getData();

        $payload['roles'] = $user->getRoles();

        if ($user && $user instanceof UserInterface) {
            $ttl = 2678400; // 60 * 60 * 24 * 31;
            $payload['exp'] = time() + $ttl;

            $payload['authentication'] = $user->getAuthentication()->getType();
            $payload['name'] = $user->getAccount()->getName();
        }

        $event->setData($payload);
    }
}
