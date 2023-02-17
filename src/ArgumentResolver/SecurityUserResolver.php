<?php

namespace Code202\Security\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Code202\Security\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityUserResolver implements ArgumentValueResolverInterface
{
    protected TokenStorageInterface $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($argument->getType() !== UserInterface::class) {
            return false;
        }

        return $this->tokenStorage->getToken()?->getUser() instanceof UserInterface;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->tokenStorage->getToken()->getUser();
    }
}
