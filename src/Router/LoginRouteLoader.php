<?php

namespace Code202\Security\Router;

use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Code202\Security\Authenticator\AuthenticatorInterface;

class LoginRouteLoader implements RouteLoaderInterface
{
    protected iterable $registers;

    public function __construct(
        #[TaggedIterator('code202.security.router.login_route_register')] iterable $registers
    ) {
        $this->registers = $registers;
    }


    public function loadRoutes(): RouteCollection
    {
        $collection = new RouteCollection();

        foreach ($this->registers as $register) {
            $collection->addCollection($register->getCollection());
        }

        return $collection;
    }
}
