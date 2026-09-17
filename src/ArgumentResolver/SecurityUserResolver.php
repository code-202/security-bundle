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
    public function __construct(
        protected TokenStorageInterface $tokenStorage
    ) {}

    /**
     * @return iterable<UserInterface>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (UserInterface::class !== $argument->getType()) {
            return [];
        }

        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof UserInterface) {
            return [$user];
        }

        throw new AccessDeniedException(sprintf('The logged-in user is not an instance of "%s".', $argument->getType()));
    }
}
