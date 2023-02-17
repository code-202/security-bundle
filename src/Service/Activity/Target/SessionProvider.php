<?php

namespace Code202\Security\Service\Activity\Target;

use Doctrine\ORM\EntityManagerInterface;
use Code202\Security\Entity\Account;
use Code202\Security\Entity\Activity\Target;
use Code202\Security\Entity\Activity\TargetReference;
use Code202\Security\Entity\Activity\TargetSession;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\Session;

class SessionProvider implements ProviderInterface
{
    protected EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    public function supports(TargetReference $reference): bool
    {
        return $reference instanceof Session;
    }

    public function get(TargetReference $reference): Target
    {
        $repository = $this->em->getRepository(TargetSession::class);

        $res = $repository->findOneBy([
            'reference' => $reference
        ]);

        if (!$res) {
            $res = new TargetSession($reference);
        }

        return $res;
    }

    public function findAll(TargetReference $reference): array
    {
        $repository = $this->em->getRepository(TargetSession::class);

        $qb = $repository->createQueryBuilder('ts')
            ->setParameter('reference', $reference);

        if ($reference instanceof Session) {
            $qb = $repository->createQueryBuilder('ts')
                ->andWhere('ts.reference = :reference')
                ->setParameter('reference', $reference);

            return $qb->getQuery()->getResult();
        }

        if ($reference instanceof Authentication) {
            $qb = $repository->createQueryBuilder('ts')
                ->innerJoin('ts.reference', 's')
                ->andWhere('s.authentication = :reference')
                ->setParameter('reference', $reference);

            return $qb->getQuery()->getResult();
        }

        if ($reference instanceof Account) {
            $qb = $repository->createQueryBuilder('ts')
                ->innerJoin('ts.reference', 's')
                ->innerJoin('s.authentication', 'a')
                ->andWhere('a.account = :reference')
                ->setParameter('reference', $reference);

            return $qb->getQuery()->getResult();
        }

        return [];
    }
}
