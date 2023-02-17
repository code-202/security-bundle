<?php

namespace Code202\Security\Event\Account;

use Code202\Security\Entity\Account;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class AbstractAccountEvent extends GenericEvent
{
    private Account $account;

    public function __construct(Account $account, array $arguments = [])
    {
        parent::__construct($account, $arguments);
        $this->account = $account;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }
}
