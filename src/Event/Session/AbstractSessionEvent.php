<?php

namespace Code202\Security\Event\Session;

use Code202\Security\Entity\Session;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class AbstractSessionEvent extends GenericEvent
{
    private Session $session;

    public function __construct(Session $session, array $arguments = [])
    {
        parent::__construct($session, $arguments);
        $this->session = $session;
    }

    public function getSession(): Session
    {
        return $this->session;
    }
}
