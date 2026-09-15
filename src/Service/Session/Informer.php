<?php

namespace Code202\Security\Service\Session;

use Code202\Security\Entity\Account;
use Code202\Security\Entity\Session;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class Informer
{
    public function __construct(protected EntityManagerInterface $em) {}

    /**
     * @return array{
     *   nbActives: int,
     *   nbExpired: int,
     * }
     */
    public function getSummary(Account $account): array
    {
        $qb = $this->em->getRepository(Session::class)
            ->createQueryBuilder('s')
            ->select('SUM(CASE WHEN s.expiredAt IS NULL OR s.expiredAt > :now THEN 1 ELSE 0 END) AS nbActives')
            ->addSelect('SUM(CASE WHEN s.expiredAt IS NOT NULL AND s.expiredAt < :now THEN 1 ELSE 0 END) AS nbExpired')
            ->innerJoin('s.authentication', 'a')
            ->andWhere('a.account = :account')
            ->setParameter('account', $account)
            ->setParameter('now', new DateTime())
        ;

        return $qb->getQuery()->getSingleResult();
    }
}
