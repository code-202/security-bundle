<?php

namespace Code202\Security\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoginFactory extends AbstractFactory
{
    public const PRIORITY = -40;

    protected array $factories = [];

    public function __construct()
    {
        $options['check_path'] = '/login_check';
        $options['login_path'] = '/login';
        $this->defaultFailureHandlerOptions = [];
        $this->defaultSuccessHandlerOptions = [];

        $this->addFactory(new TokenByEmailFormLoginFactory());
        $this->addFactory(new TokenByEmailJsonLoginFactory());
        $this->addFactory(new UsernamePasswordFormLoginFactory());
        $this->addFactory(new UsernamePasswordJsonLoginFactory());
    }

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function getKey(): string
    {
        return 'code202-login';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $builder = $node->children();

        foreach ($this->factories as $factory) {
            $n = $builder->arrayNode($factory->getShortKey());
            $n->canBeEnabled();

            $keys = array_keys($this->options);
            $keys = array_filter($keys, fn ($key) => $key !== 'check_path' && $key !== 'login_path');
            $factory->addShortConfiguration($n, $keys);
            $n->end();
        }
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): array
    {
        $authenticatorIds = [];

        $base = $config;
        $checkPaths = [];
        foreach ($this->factories as $factory) {
            unset($base[$factory->getShortKey()]);

            $c = $config[$factory->getShortKey()];

            if (!$c['enabled']) {
                continue;
            }

            if (isset($checkPaths[$c['check_path']])) {
                $checkPaths[$c['check_path']]++;
            } else {
                $checkPaths[$c['check_path']] = 1;
            }
        }

        foreach ($this->factories as $factory) {
            $c = $config[$factory->getShortKey()];

            if (!$c['enabled']) {
                continue;
            }

            $authenticatorId = $factory->createAuthenticator(
                $container,
                $firewallName,
                array_merge($base, $c, [
                    'check_path' => $base['check_path'] . $c['check_path'],
                    'login_path' => $base['login_path'] . (isset($c['login_path']) ? $c['login_path'] : $c['check_path']),
                    'route_merged' => $checkPaths[$c['check_path']] > 1,
                ]),
                $userProviderId
            );

            $authenticatorIds = array_merge($authenticatorIds, is_array($authenticatorId) ? $authenticatorId : [$authenticatorId]);
        }

        return $authenticatorIds;
    }

    protected function addFactory(SubLoginFactoryInterface $factory): self
    {
        $this->factories[] = $factory;

        return $this;
    }
}
