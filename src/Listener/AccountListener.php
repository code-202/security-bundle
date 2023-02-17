<?php

namespace Code202\Security\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Code202\Security\Event\User\RefreshedEvent;
use Code202\Security\User\UserInterface;

#[AsEventListener(event: CheckPassportEvent::class, method: 'onCheckPassport')]
#[AsEventListener(event: RefreshedEvent::class, method: 'onUserRefreshed')]
class AccountListener
{
    public function onCheckPassport(CheckPassportEvent $event)
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $account = $user->getAccount();

        if (!$account->isEnabled()) {
            throw new AuthenticationException('account is disable');
        }
    }

    public function onUserRefreshed(RefreshedEvent $event)
    {
        $account = $event->getUser()->getAccount();

        if (!$account->isEnabled()) {
            throw new AuthenticationException('account is disable');
        }
    }
}
