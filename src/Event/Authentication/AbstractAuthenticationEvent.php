<?php

namespace Code202\Security\Event\Authentication;

use Code202\Security\Entity\Authentication;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class AbstractAuthenticationEvent extends GenericEvent
{
    private Authentication $authentication;

    public function __construct(Authentication $authentication, array $arguments = [])
    {
        parent::__construct($authentication, $arguments);
        $this->authentication = $authentication;
    }

    public function getAuthentication(): Authentication
    {
        return $this->authentication;
    }
}
