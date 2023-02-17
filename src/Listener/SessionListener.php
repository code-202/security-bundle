<?php

namespace Code202\Security\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Code202\Security\Authenticator\Passport\Badge\PermanentSessionBadge;
use Code202\Security\Entity\Session;
use Code202\Security\Event\User\RefreshedEvent;
use Code202\Security\Service\Session\TTLProvider as SessionTTLProvider;
use Code202\Security\User\UserInterface;

#[AsEventListener(event: CheckPassportEvent::class, method: 'onCheckPassport')]
#[AsEventListener(event: AuthenticationSuccessEvent::class, method: 'onAuthenticationSuccess')]
#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess', priority: -64)]
#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
#[AsEventListener(event: RefreshedEvent::class, method: 'onUserRefreshed')]
class SessionListener
{
    protected EntityManagerInterface $em;
    protected SessionTTLProvider $sessionTTLProvider;

    public function __construct(
        EntityManagerInterface $em,
        SessionTTLProvider $sessionTTLProvider
    ) {
        $this->em = $em;
        $this->sessionTTLProvider = $sessionTTLProvider;
    }

    public function onCheckPassport(CheckPassportEvent $event)
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $session = $user->getSession();

        if ($session->getExpiredAt() && $session->getExpiredAt() < new \DatetimeImmutable()) {
            throw new AuthenticationException('session expired');
        }
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $token = $event->getAuthenticationToken();

        if (!$token->getUser() instanceof UserInterface) {
            return;
        }

        $session = $token->getUser()->getSession();

        $this->updateExpiredAt($session);
    }

    public function onLoginSuccess(LoginSuccessEvent $event)
    {
        $request = $event->getRequest();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $session = $user->getSession();

        if ($request && $request->headers->has('user-agent')) {
            $session->setData('user_agent', $request->headers->get('user-agent'));
        }

        if ($event->getPassport()->hasBadge(RememberMeBadge::class)) {
            $badge =  $event->getPassport()->getBadge(RememberMeBadge::class);

            if ($badge->isEnabled()) {
                $session->setExpiredAt(null);
            }
        }

        if ($event->getPassport()->hasBadge(PermanentSessionBadge::class)) {
            $session->setExpiredAt(null);
        }

        $this->em->persist($session);
        $this->em->flush();
    }

    public function onLogout(LogoutEvent $event)
    {
        $token = $event->getToken();

        if (!$token->getUser() instanceof UserInterface) {
            return;
        }

        $session = $token->getUser()->getSession();

        if ($session) {
            $session->setExpiredAt(new \Datetime());
            $this->em->persist($session);
            $this->em->flush();
        }
    }

    public function onUserRefreshed(RefreshedEvent $event)
    {
        $session = $event->getUser()->getSession();

        if ($session->getExpiredAt() && $session->getExpiredAt() < new \DatetimeImmutable()) {
            throw new AuthenticationException('session expired');
        }

        $this->updateExpiredAt($session);
    }

    protected function updateExpiredAt(Session $session)
    {
        // Update expiredAt from now + ttl
        $now = new \DatetimeImmutable();
        $session->setUpdatedAt($now);

        if ($session->getExpiredAt() !== null) {
            $ttl = $this->sessionTTLProvider->getSessionTTL($session->getAuthentication()->getType()->value);
            $session->setExpiredAt($now->modify('+'.$ttl.' seconds'));
        }

        $this->em->persist($session);
        $this->em->flush();
    }
}
