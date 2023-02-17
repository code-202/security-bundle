<?php

namespace Code202\Security\Service\Activity\Target;

use Doctrine\ORM\EntityManagerInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Entity\Activity\Target;
use Code202\Security\Entity\Activity\TargetAccount;
use Code202\Security\Entity\Activity\TargetReference;

class AccountProvider implements ProviderInterface
{
    protected EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    public function supports(TargetReference $reference): bool
    {
        return $reference instanceof Account;
    }

    public function get(TargetReference $reference): Target
    {
        $repository = $this->em->getRepository(TargetAccount::class);

        $res = $repository->findOneBy([
            'reference' => $reference
        ]);

        if (!$res) {
            $res = new TargetAccount($reference);
        }

        return $res;
    }

    public function findAll(TargetReference $reference): array
    {
        if ($reference instanceof Account) {
            $repository = $this->em->getRepository(TargetAccount::class);

            $res = $repository->findBy([
                'reference' => $reference
            ]);

            return $res;
        }

        return [];
    }
}
