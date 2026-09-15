<?php

declare(strict_types=1);

namespace Code202\Security\Event\User;

use Code202\Security\User\UserInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class AbstractUserEvent extends GenericEvent
{
    private readonly UserInterface $user;

    public function __construct(UserInterface $user, array $arguments = [])
    {
        parent::__construct($user, $arguments);
        $this->user = $user;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
