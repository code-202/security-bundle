<?php

namespace Code202\Security\ArgumentResolver;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Code202\Security\Attribute\UuidOrMe;
use Code202\Security\Entity\Account;
use Code202\Security\User\UserInterface;

#[AutoconfigureTag('controller.argument_value_resolver', attributes: ['priority' => 150])]
class AccountMeResolver implements ValueResolverInterface
{
    protected TokenStorageInterface $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($argument->getType() !== Account::class) {
            return false;
        }

        foreach ($argument->getAttributesOfType(UuidOrMe::class) as $attribute) {
            if ($request->get($attribute->name) != 'me') {
                return false;
            }
        }

        return $this->tokenStorage->getToken()?->getUser() instanceof UserInterface;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($request, $argument)) {
            return [];
        }

        yield $this->tokenStorage->getToken()->getUser()->getAccount();
    }
}
