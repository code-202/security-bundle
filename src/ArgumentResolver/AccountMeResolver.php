<?php

namespace Code202\Security\ArgumentResolver;

use Code202\Security\Attribute\UuidOrMe;
use Code202\Security\Entity\Account;
use Code202\Security\User\User;
use Code202\Security\User\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AutoconfigureTag('controller.argument_value_resolver', attributes: ['priority' => 150])]
class AccountMeResolver implements ValueResolverInterface
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage
    ) {}

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (Account::class !== $argument->getType()) {
            return false;
        }

        foreach ($argument->getAttributesOfType(UuidOrMe::class) as $attribute) {
            if ('me' != $request->attributes->get($attribute->name)) {
                return false;
            }
        }

        return $this->tokenStorage->getToken()?->getUser() instanceof UserInterface;
    }

    /**
     * @return iterable<Account>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($request, $argument)) {
            return [];
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof User) {
            return [];
        }

        yield $user->getAccount();
    }
}
