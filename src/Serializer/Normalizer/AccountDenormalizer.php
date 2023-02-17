<?php

namespace Code202\Security\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Repository\AccountRepository;
use Code202\Security\Uuid\UuidValidatorInterface;

class AccountDenormalizer implements DenormalizerInterface
{
    protected AccountRepository $repository;
    protected UuidValidatorInterface $uuidValidator;

    public function __construct(
        AccountRepository $repository,
        UuidValidatorInterface $uuidValidator
    ) {
        $this->repository = $repository;
        $this->uuidValidator = $uuidValidator;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        return $this->repository->findOneByUuid($data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return $type === Account::class && is_string($data) && $this->uuidValidator->validate($data);
    }
}
