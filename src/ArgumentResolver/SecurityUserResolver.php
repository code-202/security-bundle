<?php

namespace Code202\Security\ArgumentResolver;

use Code202\Security\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SecurityUserResolver implements ValueResolverInterface
{
    protected TokenStorageInterface $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UserInterface::class) {
            return [];
        }

        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof UserInterface) {
            return [$user];
        }

        throw new AccessDeniedException(sprintf('The logged-in user is an instance of "%s" but a user of type "%s" is expected.', $user::class, $argument->getType()));
    }
}
