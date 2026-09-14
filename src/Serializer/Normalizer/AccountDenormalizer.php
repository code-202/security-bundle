<?php

namespace Code202\Security\Serializer\Normalizer;

use Code202\Security\Entity\Account;
use Code202\Security\Repository\AccountRepository;
use Code202\Security\Uuid\UuidValidatorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AccountDenormalizer implements DenormalizerInterface
{
    public function __construct(
        protected AccountRepository $repository,
        protected UuidValidatorInterface $uuidValidator
    ) {}

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return $this->repository->findOneByUuid($data);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Account::class === $type && is_string($data) && $this->uuidValidator->validate($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Account::class,
        ];
    }
}
