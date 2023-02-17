<?php

namespace Code202\Security\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface SubLoginFactoryInterface extends AuthenticatorFactoryInterface
{
    public function getShortKey(): string;

    public function addShortConfiguration(NodeDefinition $node, array $overrideOptions = []);
}
