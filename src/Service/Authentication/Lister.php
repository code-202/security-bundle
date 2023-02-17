<?php

namespace Code202\Security\Service\Authentication;

use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Code202\Security\Entity\Account;
use Code202\Security\Entity\Authentication;

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

        $qb = $this->em->getRepository(Authentication::class)
            ->createQueryBuilder('a')
            ->innerJoin('a.account', 'c')
            ->addSelect('c')
            ->orderBy('a.updatedAt', 'desc')
            ->addOrderBy('a.createdAt', 'desc')
        ;

        if ($options['account']) {
            $qb
                ->andWhere('c = :account')
                ->setParameter(':account', $options['account'])
            ;
        }

        if ('active' == $options['show']) {
            $qb
                ->andWhere('a.enabled = :enabled')
                ->setParameter('enabled', true)
            ;
        } elseif ('inactive' == $options['show']) {
            $qb
                ->andWhere('a.enabled = :enabled')
                ->setParameter('enabled', false)
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

        return $resolver;
    }
}
