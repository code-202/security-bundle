<?php

namespace Code202\Security\DependencyInjection;

use Code202\Security\Entity\AuthenticationType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('code202_security');

        $node = $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('uuid')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('generator')
                            ->defaultValue('')
                        ->end()
                        ->scalarNode('validator')
                            ->defaultValue('')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sessionTTL')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('default')->defaultValue(3600)->end()
        ;

        foreach (AuthenticationType::cases() as $type) {
            $node->integerNode($type->value)->end();
        }
        $node
                    ->end()
                ->end()
                ->arrayNode('token_by_email')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('refresher')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('token_generator')->defaultValue('number_base')->end()
                                ->scalarNode('minimal_refresh_interval')->defaultValue('1 minute')->end()
                                ->scalarNode('lifetime_interval')->defaultValue('5 minutes')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('token_generator')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('number_base')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('size')->defaultValue(6)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('role_strategies')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('roles')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')->cannotBeEmpty()->end()
                            ->end()
                            ->scalarNode('to_grant')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('to_revoke')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
