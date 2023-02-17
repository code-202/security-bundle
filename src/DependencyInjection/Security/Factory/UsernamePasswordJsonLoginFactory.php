<?php

namespace Code202\Security\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Code202\Security\Authenticator\UsernamePasswordJsonAuthenticator;

class UsernamePasswordJsonLoginFactory extends AbstractSubLoginFactory
{
    public function buildOptions()
    {
        $this->addOption('check_path', '/username');
        $this->addOption('username_parameter', 'key');
        $this->addOption('password_parameter', 'password');
        $this->addOption('remember_me_parameter', 'remember_me');
        $this->defaultFailureHandlerOptions = [];
        $this->defaultSuccessHandlerOptions = [];
    }

    public function getShortKey(): string
    {
        return 'username_password_json';
    }

    public function getAuthenticatorClass(): string
    {
        return UsernamePasswordJsonAuthenticator::class;
    }

    public function getRegisterAction(bool $merged): string
    {
        return $merged ? 'username' : 'usernameJson';
    }
}
