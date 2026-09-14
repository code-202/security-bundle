<?php

namespace Code202\Security\User;

use Code202\Security\Entity\Account;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\Session;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    protected array $datas;

    public function __construct(
        protected Session $session
    ) {}

    public function getRoles(): array
    {
        return $this->getAccount()->getRoles();
    }

    public function getPassword(): ?string
    {
        return $this->getData('password');
    }

    public function getUserIdentifier(): string
    {
        return $this->session->getUuid();
    }

    public function eraseCredentials(): void
    {
        $this->datas = [];
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getAuthentication(): Authentication
    {
        return $this->session->getAuthentication();
    }

    public function getAccount(): Account
    {
        return $this->session->getAuthentication()->getAccount();
    }

    public function getDatas(): array
    {
        return $this->getAuthentication()->getDatas();

        return $this->datas;
    }

    public function getData($key): mixed
    {
        return $this->getAuthentication()->getData($key);
        if (isset($this->datas[$key])) {
            return $this->datas[$key];
        }

        return null;
    }
}
