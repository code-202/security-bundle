<?php

namespace Code202\Security\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Code202\Security\Authenticator\TokenByEmailFormAuthenticator;

class TokenByEmailFormLoginFactory extends AbstractSubLoginFactory
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
        return 'token_by_email_form';
    }

    public function getAuthenticatorClass(): string
    {
        return TokenByEmailFormAuthenticator::class;
    }

    public function getRegisterAction(bool $merged): string
    {
        return $merged ? 'email' : 'emailForm';
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string
    {
        $authenticatorId = parent::createAuthenticator($container, $firewallName, $config, $userProviderId);
        $options = array_intersect_key($config, $this->options);

        $definition = $container->getDefinition($authenticatorId);

        return $authenticatorId;
    }
}
