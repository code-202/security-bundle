<?php

namespace Code202\Security\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Code202\Security\Router\LoginRouteRegister;
use Code202\Security\Service\Common\TokenGeneratorInterface;
use Code202\Security\Uuid\UuidGeneratorInterface;
use Code202\Security\Uuid\UuidValidatorInterface;

class Code202SecurityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->loadTTLProvider($config, $container);

        $this->configureUuidGenerator($config, $container);

        $this->configureUuidValidator($config, $container);

        $this->configureTokenGenerator($config, $container);

        $this->configureTokenByEmailRefresher($config, $container);

        $this->configureNumberBaseTokenGenerator($config, $container);

        $this->configureRoleManager($config, $container);
    }

    protected function loadTTLProvider(array $config, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('Code202\Security\Service\Session\TTLProvider');
        $definition->setArgument('$config', $config['sessionTTL']);
    }

    protected function configureUuidGenerator(array $config, ContainerBuilder $container): void
    {
        $uuidGeneratorClass = match ($config['uuid']['generator']) {
            'ramsey/uuid' => \Code202\Security\Bridge\Ramsey\Uuid\UuidGenerator::class,
            'symfony/polyfill-uuid' => \Code202\Security\Bridge\Symfony\Polyfill\Uuid\UuidGenerator::class,
            default => $config['uuid']['generator'],
        };

        if ($uuidGeneratorClass) {
            $container->autowire($uuidGeneratorClass);
            $container->setAlias(UuidGeneratorInterface::class, $uuidGeneratorClass);
        }
    }

    protected function configureUuidValidator(array $config, ContainerBuilder $container): void
    {
        $uuidValidatorClass = match ($config['uuid']['validator']) {
            'ramsey/uuid' => \Code202\Security\Bridge\Ramsey\Uuid\UuidValidator::class,
            'symfony/polyfill-uuid' => \Code202\Security\Bridge\Symfony\Polyfill\Uuid\UuidValidator::class,
            default => $config['uuid']['validator'],
        };

        if ($uuidValidatorClass) {
            $container->autowire($uuidValidatorClass);
            $container->setAlias(UuidValidatorInterface::class, $uuidValidatorClass);
        }
    }

    protected function configureTokenGenerator(array $config, ContainerBuilder $container): void
    {
        $tokenGeneratorClass = match ($config['token_by_email']['refresher']['token_generator']) {
            'number_base' => \Code202\Security\Service\Common\NumberBaseTokenGenerator::class,
            default => $config['token_by_email']['refresher']['token_generator'],
        };

        if ($tokenGeneratorClass) {
            $container->autowire($tokenGeneratorClass);
            $container->setAlias(TokenGeneratorInterface::class, $tokenGeneratorClass);
        }
    }

    protected function configureTokenByEmailRefresher(array $config, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('Code202\Security\Service\Authentication\TokenByEmailRefresher');
        $definition->setArgument('$minimalRefreshInterval', $config['token_by_email']['refresher']['minimal_refresh_interval']);
        $definition->setArgument('$lifetimeInterval', $config['token_by_email']['refresher']['lifetime_interval']);
    }

    protected function configureNumberBaseTokenGenerator(array $config, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(\Code202\Security\Service\Common\NumberBaseTokenGenerator::class);
        $definition->setArgument('$size', $config['token_generator']['number_base']['size']);
    }

    protected function configureRoleManager(array $config, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(\Code202\Security\Service\RoleStrategy\Provider::class);
        $definition->setArgument('$strategies', $config['role_strategies']);
    }
}
