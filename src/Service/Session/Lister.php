<?php

namespace Code202\Security\Service\Session;

use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Code202\Security\Entity\Account;
use Code202\Security\Entity\Authentication;
use Code202\Security\Entity\Session;

class Lister
{
    protected $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    public function get(array $options): Pagerfanta
    {
        $options = $this->createOptionResolver()->resolve($options);

        $qb = $this->em->getRepository(Session::class)
            ->createQueryBuilder('s')
            ->innerJoin('s.authentication', 'a')
            ->innerJoin('a.account', 'c')
            ->addSelect('a')
            ->addSelect('c')
            ->orderBy('s.updatedAt', 'desc')
            ->addOrderBy('s.expiredAt', 'desc')
            ->addOrderBy('s.createdAt', 'desc')
        ;

        if ($options['account']) {
            $qb
                ->andWhere('c = :account')
                ->setParameter(':account', $options['account'])
            ;
        }

        if ($options['authentication']) {
            $qb
                ->andWhere('a = :authentication')
                ->setParameter(':authentication', $options['authentication'])
            ;
        }

        if ('active' == $options['show']) {
            $qb
                ->andWhere('s.expiredAt IS NULL OR s.expiredAt > :now')
                ->setParameter('now', new \Datetime())
            ;
        } elseif ('inactive' == $options['show']) {
            $qb
                ->andWhere('s.expiredAt IS NOT NULL AND s.expiredAt < :now')
                ->setParameter('now', new \Datetime())
            ;
        }

        $adapter = new QueryAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($options['maxPerPage']);
        $pagerfanta->setCurrentPage($options['page']);

        return $pagerfanta;
    }

    protected function createOptionResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();

        $resolver->define('page')
            ->default(1)
            ->allowedTypes('int')
        ;

        $resolver->define('maxPerPage')
            ->default(10)
            ->allowedTypes('int')
        ;

        $resolver->define('show')
            ->default('all')
            ->allowedValues('all', 'active', 'inactive')
        ;

        $resolver->define('account')
            ->default(null)
            ->allowedTypes('null', Account::class)
        ;

        $resolver->define('authentication')
            ->default(null)
            ->allowedTypes('null', Authentication::class)
        ;


        return $resolver;
    }
}
