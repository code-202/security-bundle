<?php

namespace Code202\Security\DependencyInjection;

use Code202\Security\Bridge\Ramsey\Uuid\UuidGenerator;
use Code202\Security\Bridge\Ramsey\Uuid\UuidValidator;
use Code202\Security\Service\Authentication\TokenByEmailRefresher;
use Code202\Security\Service\Common\NumberBaseTokenGenerator;
use Code202\Security\Service\Common\TokenGeneratorInterface;
use Code202\Security\Service\RoleStrategy\Provider;
use Code202\Security\Service\Session\Truster;
use Code202\Security\Service\Session\TTLProvider;
use Code202\Security\Uuid\UuidGeneratorInterface;
use Code202\Security\Uuid\UuidValidatorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class Code202SecurityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
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

        $this->configureSessionTruster($config, $container);
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function loadTTLProvider(array $config, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(TTLProvider::class);
        $definition->setArgument('$config', $config['sessionTTL']);
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function configureUuidGenerator(array $config, ContainerBuilder $container): void
    {
        $uuidGeneratorClass = match ($config['uuid']['generator']) {
            'ramsey/uuid' => UuidGenerator::class,
            'symfony/polyfill-uuid' => \Code202\Security\Bridge\Symfony\Polyfill\Uuid\UuidGenerator::class,
            default => $config['uuid']['generator'],
        };

        if ($uuidGeneratorClass) {
            $container->autowire($uuidGeneratorClass);
            $container->setAlias(UuidGeneratorInterface::class, $uuidGeneratorClass);
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function configureUuidValidator(array $config, ContainerBuilder $container): void
    {
        $uuidValidatorClass = match ($config['uuid']['validator']) {
            'ramsey/uuid' => UuidValidator::class,
            'symfony/polyfill-uuid' => \Code202\Security\Bridge\Symfony\Polyfill\Uuid\UuidValidator::class,
            default => $config['uuid']['validator'],
        };

        if ($uuidValidatorClass) {
            $container->autowire($uuidValidatorClass);
            $container->setAlias(UuidValidatorInterface::class, $uuidValidatorClass);
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function configureTokenGenerator(array $config, ContainerBuilder $container): void
    {
        $tokenGeneratorClass = match ($config['token_by_email']['refresher']['token_generator']) {
            'number_base' => NumberBaseTokenGenerator::class,
            default => $config['token_by_email']['refresher']['token_generator'],
        };

        if ($tokenGeneratorClass) {
            $container->autowire($tokenGeneratorClass);
            $container->setAlias(TokenGeneratorInterface::class, $tokenGeneratorClass);
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function configureTokenByEmailRefresher(array $config, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(TokenByEmailRefresher::class);
        $definition->setArgument('$minimalRefreshInterval', $config['token_by_email']['refresher']['minimal_refresh_interval']);
        $definition->setArgument('$lifetimeInterval', $config['token_by_email']['refresher']['lifetime_interval']);
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function configureNumberBaseTokenGenerator(array $config, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(NumberBaseTokenGenerator::class);
        $definition->setArgument('$size', $config['token_generator']['number_base']['size']);
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function configureRoleManager(array $config, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(Provider::class);
        $definition->setArgument('$strategies', $config['role_strategies']);
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function configureSessionTruster(array $config, ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(Truster::class);
        $definition->setArgument('$trustDuration', $config['trust_duration']);
    }
}
