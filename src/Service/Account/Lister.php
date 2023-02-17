<?php

namespace Code202\Security\Service\Account;

use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Code202\Security\Entity\Account;

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

        $qb = $this->em->getRepository(Account::class)
            ->createQueryBuilder('a')
        ;

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

        if ('name' == $options['sort']) {
            $qb
                ->orderBy('a.name', 'asc')
            ;
        } elseif ('date' == $options['sort']) {
            $qb
                ->orderBy('a.createdAt', 'desc')
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

        $resolver->define('sort')
            ->default('name')
            ->allowedValues('name', 'date')
        ;

        return $resolver;
    }
}
