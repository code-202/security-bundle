<?php

namespace Code202\Security\Service\Activity\Target;

use Code202\Security\Entity\Account;
use Code202\Security\Entity\Activity\Target;
use Code202\Security\Entity\Activity\TargetAuthentication;
use Code202\Security\Entity\Activity\TargetReference;
use Code202\Security\Entity\Authentication;
use Doctrine\ORM\EntityManagerInterface;

class AuthenticationProvider implements ProviderInterface
{
    public function __construct(
        protected EntityManagerInterface $em
    ) {}

    public function supports(TargetReference $reference): bool
    {
        return $reference instanceof Authentication;
    }

    public function get(TargetReference $reference): Target
    {
        $repository = $this->em->getRepository(TargetAuthentication::class);

        $res = $repository->findOneBy([
            'reference' => $reference,
        ]);

        if (!$res instanceof TargetAuthentication) {
            return new TargetAuthentication($reference);
        }

        return $res;
    }

    public function findAll(TargetReference $reference): array
    {
        $repository = $this->em->getRepository(TargetAuthentication::class);

        if ($reference instanceof Authentication) {
            $qb = $repository->createQueryBuilder('ta')
                ->andWhere('ta.reference = :reference')
                ->setParameter('reference', $reference)
            ;

            return $qb->getQuery()->getResult();
        }

        if ($reference instanceof Account) {
            $qb = $repository->createQueryBuilder('ta')
                ->innerJoin('ta.reference', 'a')
                ->andWhere('a.account = :reference')
                ->setParameter('reference', $reference)
            ;

            return $qb->getQuery()->getResult();
        }

        return [];
    }
}
