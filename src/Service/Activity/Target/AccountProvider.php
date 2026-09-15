<?php

namespace Code202\Security\Service\Activity\Target;

use Code202\Security\Entity\Account;
use Code202\Security\Entity\Activity\Target;
use Code202\Security\Entity\Activity\TargetAccount;
use Code202\Security\Entity\Activity\TargetReference;
use Code202\Security\Exception\LogicException;
use Doctrine\ORM\EntityManagerInterface;

class AccountProvider implements ProviderInterface
{
    public function __construct(
        protected EntityManagerInterface $em
    ) {}

    public function supports(TargetReference $reference): bool
    {
        return $reference instanceof Account;
    }

    public function get(TargetReference $reference): Target
    {
        if (!$reference instanceof Account) {
            throw new LogicException('reference is not an account');
        }

        $repository = $this->em->getRepository(TargetAccount::class);

        $res = $repository->findOneBy([
            'reference' => $reference,
        ]);

        if (!$res instanceof TargetAccount) {
            return new TargetAccount($reference);
        }

        return $res;
    }

    public function findAll(TargetReference $reference): array
    {
        if ($reference instanceof Account) {
            $repository = $this->em->getRepository(TargetAccount::class);

            return $repository->findBy([
                'reference' => $reference,
            ]);
        }

        return [];
    }
}
