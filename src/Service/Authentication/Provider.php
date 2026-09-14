<?php

namespace Code202\Security\Service\Authentication;

use Code202\Security\Entity\Account;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\AuthenticationType;
use Doctrine\ORM\EntityManagerInterface;

class Provider
{
    protected $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    public function getOne(Account $account, AuthenticationType $type): ?Authentication
    {
        $qb = $this->em->getRepository(Authentication::class)
            ->createQueryBuilder('a')
            ->andWhere('a.account = :account')
            ->setParameter('account', $account)
            ->andWhere('a.type = :type')
            ->setParameter('type', $type)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOne(string $key, AuthenticationType $type): ?Authentication
    {
        $qb = $this->em->getRepository(Authentication::class)
            ->createQueryBuilder('a')
            ->andWhere('a.type = :type')
            ->setParameter('type', $type)
            ->andWhere('a.key = :key')
            ->setParameter('key', $key)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
