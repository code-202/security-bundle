<?php

declare(strict_types=1);

namespace Code202\Security\Router;

use Symfony\Component\Routing\RouteCollection;

interface LoginRouteRegisterInterface
{
    public function register(string $name, string $route, string $method, string $controller, string $alias): void;

    public function getCollection(): RouteCollection;
}
