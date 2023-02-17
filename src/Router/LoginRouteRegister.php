<?php

namespace Code202\Security\Router;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class LoginRouteRegister implements LoginRouteRegisterInterface
{
    protected RouteCollection $routes;

    public function __construct()
    {
        $this->collection = new RouteCollection();
    }

    public function register(string $name, string $route, string $method, string $controller, string $alias)
    {
        $this->collection->add($name, new Route(
            $route,
            [ '_controller' => $controller],
            [],
            [],
            '',
            [],
            $method
        ));
        if ($alias != $name) {
            $this->collection->addAlias($alias, $name);
        }
    }

    public function getCollection(): RouteCollection
    {
        return $this->collection;
    }
}
