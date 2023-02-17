<?php

namespace Code202\Security\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Code202\Security\Router\LoginRouteRegister;

abstract class AbstractSubLoginFactory extends AbstractFactory implements SubLoginFactoryInterface
{
    public const PRIORITY = -40;

    public function __construct()
    {
        $this->buildOptions();
    }

    public function buildOptions()
    {
    }

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    abstract public function getShortKey(): string;

    public function getKey(): string
    {
        return 'code202_'.$this->getShortKey().'_login';
    }

    public function getAuthenticatorId(string $firewallName): string
    {
        return 'security.authenticator.'.$this->getKey().'.'.$firewallName;
    }

    public function addShortConfiguration(NodeDefinition $node, array $overrideOptions = [])
    {
        $builder = $node->children();

        foreach (array_merge($this->options, $this->defaultSuccessHandlerOptions, $this->defaultFailureHandlerOptions) as $name => $default) {
            if (!in_array($name, $overrideOptions)) {
                if (\is_bool($default)) {
                    $builder->booleanNode($name)->defaultValue($default);
                } else {
                    $builder->scalarNode($name)->defaultValue($default);
                }
            }
        }
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string
    {
        $authenticatorId = $this->getAuthenticatorId($firewallName);
        $options = array_intersect_key($config, $this->options);

        $container
            ->setDefinition($authenticatorId, new Definition($this->getAuthenticatorClass()))
            ->setArgument('$httpUtils', new Reference('security.http_utils'))
            ->setArgument('$userProvider', new Reference($userProviderId))
            ->setArgument('$successHandler', isset($config['success_handler']) ? new Reference($this->createAuthenticationSuccessHandler($container, $firewallName, $config)) : null)
            ->setArgument('$failureHandler', isset($config['failure_handler']) ? new Reference($this->createAuthenticationFailureHandler($container, $firewallName, $config)) : null)
            ->setArgument('$options', $options)
            ->setArgument('$propertyAccessor', new Reference('property_accessor'))
        ;

        $container
            ->setDefinition($authenticatorId.'.login_route_register', new Definition(LoginRouteRegister::class))
            ->addMethodCall('register', ['.login.'.$this->getRegisterAction($config['route_merged']), $options['check_path'], 'post', 'Code202\Security\Controller\LoginController::'.$this->getRegisterAction($config['route_merged']), '.login.'.$this->getRegisterAction(false)])
            ->addTag('code202.security.router.login_route_register')
        ;

        return $authenticatorId;
    }

    abstract public function getAuthenticatorClass(): string;

    abstract public function getRegisterAction(bool $merged): string;
}
