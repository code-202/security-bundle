<?php

declare(strict_types=1);

namespace Code202\Security\Event\Session;

use Code202\Security\Entity\Session;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class AbstractSessionEvent extends GenericEvent
{
    private readonly Session $session;

    /**
     * @param array<string, mixed> $arguments
     */
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
