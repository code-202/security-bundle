<?php

namespace Code202\Security\User;

use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Code202\Security\Entity\Session;

interface UserInterface extends BaseUserInterface
{
    public function getAccount(): Account;

    public function getAuthentication(): Authentication;

    public function getSession(): Session;

    public function getData($key): mixed;
}
