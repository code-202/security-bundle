<?php

namespace Code202\Security\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Code202\Security\Authenticator\UsernamePasswordFormAuthenticator;

class UsernamePasswordFormLoginFactory extends AbstractSubLoginFactory
{
    public function buildOptions()
    {
        $this->addOption('check_path', '/username');
        $this->addOption('login_path', '');
        $this->addOption('username_parameter', 'key');
        $this->addOption('password_parameter', 'password');
        $this->addOption('remember_me_parameter', 'remember_me');
        $this->defaultFailureHandlerOptions = [];
        $this->defaultSuccessHandlerOptions = [];
    }

    public function getShortKey(): string
    {
        return 'username_password_form';
    }

    public function getAuthenticatorClass(): string
    {
        return UsernamePasswordFormAuthenticator::class;
    }

    public function getRegisterAction(bool $merged): string
    {
        return $merged ? 'username' : 'usernameForm';
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string
    {
        $authenticatorId = parent::createAuthenticator($container, $firewallName, $config, $userProviderId);
        $options = array_intersect_key($config, $this->options);

        $definition = $container->getDefinition($authenticatorId);

        return $authenticatorId;
    }
}
