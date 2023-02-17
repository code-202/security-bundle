<?php

namespace Code202\Security\Router;

use Symfony\Component\Routing\RouteCollection;

interface LoginRouteRegisterInterface
{
    public function register(string $name, string $route, string $method, string $controller, string $alias);
    public function getCollection(): RouteCollection;
}
