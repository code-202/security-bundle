<?php

declare(strict_types=1);

namespace Code202\Security\User;

use Code202\Security\Entity\Account;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\Session;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface
{
    public function getAccount(): Account;

    public function getAuthentication(): Authentication;

    public function getSession(): Session;

    public function getData(string $key): mixed;
}
