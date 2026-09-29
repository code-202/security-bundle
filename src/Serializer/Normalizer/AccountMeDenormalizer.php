<?php

namespace Code202\Security\Serializer\Normalizer;

use Code202\Security\Entity\Account;
use Code202\Security\User\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AccountMeDenormalizer implements DenormalizerInterface
{
    public function __construct(
        protected TokenStorageInterface $tokenStorage,
    ) {}

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof User) {
            return null;
        }

        return $user->getAccount();
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Account::class === $type && is_string($data) && 'me' == $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Account::class => true,
        ];
    }
}
