<?php

namespace Code202\Security;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Code202\Security\DependencyInjection\Security\Factory\LoginFactory;
use Code202\Security\DependencyInjection\Security\Factory\TokenByEmailFormLoginFactory;
use Code202\Security\DependencyInjection\Security\Factory\TokenByEmailJsonLoginFactory;
use Code202\Security\DependencyInjection\Security\Factory\UsernamePasswordFormLoginFactory;
use Code202\Security\DependencyInjection\Security\Factory\UsernamePasswordJsonLoginFactory;

class Code202SecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addAuthenticatorFactory(new LoginFactory());
        $extension->addAuthenticatorFactory(new TokenByEmailFormLoginFactory());
        $extension->addAuthenticatorFactory(new TokenByEmailJsonLoginFactory());
        $extension->addAuthenticatorFactory(new UsernamePasswordFormLoginFactory());
        $extension->addAuthenticatorFactory(new UsernamePasswordJsonLoginFactory());
    }
}
