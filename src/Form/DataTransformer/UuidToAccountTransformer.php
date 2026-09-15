<?php

namespace Code202\Security\Form\DataTransformer;

use Code202\Security\Entity\Account;
use Code202\Security\Repository\AccountRepository;
use Code202\Security\User\UserInterface;
use Code202\Security\Uuid\UuidValidatorInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @implements DataTransformerInterface<null|Account, string>
 */
class UuidToAccountTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly AccountRepository $repository,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UuidValidatorInterface $uuidValidator
    ) {}

    public function transform($account): string
    {
        if (null === $account) {
            return '';
        }

        return $account->getUuid();
    }

    public function reverseTransform($uuid): ?Account
    {
        // no issue number? It's optional, so that's ok
        if (!$uuid) {
            return null;
        }

        if ('me' == $uuid) {
            $user = $this->tokenStorage->getToken()->getUser();
            if (!$user instanceof UserInterface) {
                throw new TransformationFailedException(
                    'Me user is no good !',
                );
            }

            return $user->getAccount();
        }

        if (!$this->uuidValidator->validate($uuid)) {
            throw new TransformationFailedException(sprintf(
                'Uuid "%s" is malformed !',
                $uuid
            ));
        }

        $account = $this->repository->findOneByUuid($uuid);

        if (null === $account) {
            throw new TransformationFailedException(sprintf(
                'An account with uuid "%s" does not exist!',
                $uuid
            ));
        }

        return $account;
    }
}
