<?php

namespace Code202\Security\Event\User;

use Code202\Security\User\UserInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class AbstractUserEvent extends GenericEvent
{
    public function __construct(UserInterface $user, array $arguments = [])
    {
        parent::__construct($user, $arguments);
        $this->account = $user;
    }

    public function getUser(): UserInterface
    {
        return $this->account;
    }
}
