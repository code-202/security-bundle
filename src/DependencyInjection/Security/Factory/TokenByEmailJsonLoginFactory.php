<?php

namespace Code202\Security\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Code202\Security\Authenticator\TokenByEmailJsonAuthenticator;

class TokenByEmailJsonLoginFactory extends AbstractSubLoginFactory
{
    public function buildOptions()
    {
        $this->addOption('check_path', '/email');
        $this->addOption('username_parameter', 'key');
        $this->addOption('password_parameter', 'password');
        $this->defaultFailureHandlerOptions = [];
        $this->defaultSuccessHandlerOptions = [];
    }

    public function getShortKey(): string
    {
        return 'token_by_email_json';
    }

    public function getAuthenticatorClass(): string
    {
        return TokenByEmailJsonAuthenticator::class;
    }

    public function getRegisterAction(bool $merged): string
    {
        return $merged ? 'email' : 'emailJson';
    }
}
