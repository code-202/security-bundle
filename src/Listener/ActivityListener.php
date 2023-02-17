<?php

namespace Code202\Security\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Code202\Security\Entity\Activity\Activity;
use Code202\Security\Entity\Activity\Type as ActivityType;
use Code202\Security\Event\Account as AccountEvent;
use Code202\Security\Event\Authentication as AuthenticationEvent;
use Code202\Security\Event\Session as SessionEvent;
use Code202\Security\Service\Activity\Target\Provider as TargetProvider;
use Code202\Security\Service\Activity\Trigger\Provider as TriggerProvider;
use Code202\Security\User\UserInterface;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess')]
#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
#[AsEventListener(event: AccountEvent\CreatedEvent::class, method: 'onAccountCreated')]
#[AsEventListener(event: AccountEvent\DisabledEvent::class, method: 'onAccountDisabled')]
#[AsEventListener(event: AccountEvent\EnabledEvent::class, method: 'onAccountEnabled')]
#[AsEventListener(event: AccountEvent\GrantedEvent::class, method: 'onRoleGranted')]
#[AsEventListener(event: AccountEvent\NameChangedEvent::class, method: 'onNameChanged')]
#[AsEventListener(event: AccountEvent\RevokedEvent::class, method: 'onRoleRevoked')]
#[AsEventListener(event: AuthenticationEvent\CreatedEvent::class, method: 'onAuthenticationCreated')]
#[AsEventListener(event: AuthenticationEvent\PasswordChangedEvent::class, method: 'onPasswordChanged')]
#[AsEventListener(event: AuthenticationEvent\UsernameChangedEvent::class, method: 'onUsernameChanged')]
#[AsEventListener(event: AuthenticationEvent\TokenByEmailRefreshedEvent::class, method: 'onTokenByEmailRefreshed')]
#[AsEventListener(event: AuthenticationEvent\TokenByEmailVerifiedEvent::class, method: 'onTokenByEmailVerified')]
#[AsEventListener(event: AuthenticationEvent\EmailChangedEvent::class, method: 'onEmailChanged')]
#[AsEventListener(event: SessionEvent\DeletedEvent::class, method: 'onSessionDeleted')]
class ActivityListener
{
    protected EntityManagerInterface $em;

    protected TargetProvider $targetProvider;

    protected TriggerProvider $triggerProvider;

    public function __construct(
        EntityManagerInterface $em,
        TargetProvider $targetProvider,
        TriggerProvider $triggerProvider
    ) {
        $this->em = $em;
        $this->targetProvider = $targetProvider;
        $this->triggerProvider = $triggerProvider;
    }

    public function onLoginSuccess(LoginSuccessEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $session = $user->getSession();
        $authentication = $user->getAuthentication();

        if (!$session->isCreated()) {
            return;
        }

        $target = $this->targetProvider->get($authentication);
        $activity = new Activity(ActivityType::LOGIN, $target, $this->triggerProvider->get());

        $this->em->persist($activity);
        $this->em->flush();
    }

    public function onLogout(LogoutEvent $event)
    {
        $user = $event->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $authentication = $user->getAuthentication();

        $target = $this->targetProvider->get($authentication);
        $activity = new Activity(ActivityType::LOGOUT, $target, $this->triggerProvider->get());

        $this->em->persist($activity);
        $this->em->flush();
    }

    public function onAccountCreated(AccountEvent\CreatedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAccount());
        $activity = new Activity(ActivityType::ACCOUNT_CREATED, $target, $this->triggerProvider->get());

        $this->em->persist($activity);
    }

    public function onAccountEnabled(AccountEvent\EnabledEvent $event)
    {
        $target = $this->targetProvider->get($event->getAccount());
        $activity = new Activity(ActivityType::ACCOUNT_ENABLED, $target, $this->triggerProvider->get());
        $activity->setDatas($event->getArguments());

        $this->em->persist($activity);
    }

    public function onAccountDisabled(AccountEvent\DisabledEvent $event)
    {
        $target = $this->targetProvider->get($event->getAccount());
        $activity = new Activity(ActivityType::ACCOUNT_DISABLED, $target, $this->triggerProvider->get());
        $activity->setDatas($event->getArguments());

        $this->em->persist($activity);
    }

    public function onRoleGranted(AccountEvent\GrantedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAccount());
        $activity = new Activity(ActivityType::ROLE_GRANTED, $target, $this->triggerProvider->get());
        $activity->setDatas($event->getArguments());

        $this->em->persist($activity);
    }

    public function onNameChanged(AccountEvent\NameChangedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAccount());
        $activity = new Activity(ActivityType::ACCOUNT_NAME_CHANGED, $target, $this->triggerProvider->get());
        $activity->setDatas($event->getArguments());

        $this->em->persist($activity);
    }

    public function onRoleRevoked(AccountEvent\RevokedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAccount());
        $activity = new Activity(ActivityType::ROLE_REVOKED, $target, $this->triggerProvider->get());
        $activity->setDatas($event->getArguments());

        $this->em->persist($activity);
    }

    public function onAuthenticationCreated(AuthenticationEvent\CreatedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAuthentication());
        $activity = new Activity(ActivityType::AUTHENTICATION_CREATED, $target, $this->triggerProvider->get());

        $this->em->persist($activity);
    }

    public function onPasswordChanged(AuthenticationEvent\PasswordChangedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAuthentication());
        $activity = new Activity(ActivityType::PASSWORD_CHANGED, $target, $this->triggerProvider->get());
        $activity->setDatas($event->getArguments());

        $this->em->persist($activity);
    }

    public function onUsernameChanged(AuthenticationEvent\UsernameChangedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAuthentication());
        $activity = new Activity(ActivityType::USERNAME_CHANGED, $target, $this->triggerProvider->get());
        $activity->setDatas($event->getArguments());

        $this->em->persist($activity);
    }

    public function onTokenByEmailRefreshed(AuthenticationEvent\TokenByEmailRefreshedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAuthentication());
        $activity = new Activity(ActivityType::TOKEN_BY_EMAIL_REFRESHED, $target, $this->triggerProvider->get());
        $arguments = $event->getArguments();
        unset($arguments['token']);
        $activity->setDatas($arguments);

        $this->em->persist($activity);
    }

    public function onSessionDeleted(SessionEvent\DeletedEvent $event)
    {
        $target = $this->targetProvider->get($event->getSession());
        $activity = new Activity(ActivityType::SESSION_DELETED, $target, $this->triggerProvider->get());

        $this->em->persist($activity);
    }

    public function onTokenByEmailVerified(AuthenticationEvent\TokenByEmailVerifiedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAuthentication());
        $activity = new Activity(ActivityType::TOKEN_BY_EMAIL_VERIFIED, $target, $this->triggerProvider->get());
        $activity->setDatas($event->getArguments());

        $this->em->persist($activity);
    }

    public function onEmailChanged(AuthenticationEvent\EmailChangedEvent $event)
    {
        $target = $this->targetProvider->get($event->getAuthentication());
        $activity = new Activity(ActivityType::EMAIL_CHANGED, $target, $this->triggerProvider->get());
        $activity->setDatas($event->getArguments());

        $this->em->persist($activity);
    }
}
