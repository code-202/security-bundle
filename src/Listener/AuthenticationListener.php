<?php

namespace Code202\Security\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Code202\Security\Authenticator\Passport\Badge\ResetPasswordAuthenticationBadge;
use Code202\Security\Authenticator\Passport\Badge\VerifyAuthenticationBadge;
use Code202\Security\Event\User\RefreshedEvent;
use Code202\Security\User\UserInterface;

#[AsEventListener(event: CheckPassportEvent::class, method: 'onCheckPassport')]
#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess')]
#[AsEventListener(event: RefreshedEvent::class, method: 'onUserRefreshed')]
class AuthenticationListener
{
    protected $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    public function onCheckPassport(CheckPassportEvent $event)
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $authentication = $user->getAuthentication();

        if (!$authentication->isEnabled()) {
            throw new AuthenticationException('authentication is disable');
        }
    }

    public function onLoginSuccess(LoginSuccessEvent $event)
    {
        $request = $event->getRequest();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $authentication = $user->getAuthentication();

        if ($event->getPassport()->hasBadge(VerifyAuthenticationBadge::class)) {
            $authentication->setVerified(true);
        }

        if ($event->getPassport()->hasBadge(ResetPasswordAuthenticationBadge::class)) {
            $authentication->setData('password', '');
        }

        $this->em->persist($authentication);
        $this->em->flush();
    }

    public function onUserRefreshed(RefreshedEvent $event)
    {
        $authentication = $event->getUser()->getAuthentication();

        if (!$authentication->isEnabled()) {
            throw new AuthenticationException('authentication is disable');
        }
    }
}
