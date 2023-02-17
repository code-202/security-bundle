<?php

namespace Code202\Security\Service\Activity;

use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Code202\Security\Entity\Activity\Activity;
use Code202\Security\Entity\Activity\Target;
use Code202\Security\Entity\Activity\Trigger;

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

        $qb = $this->em->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->innerJoin('a.target', 't')
            ->innerJoin('a.trigger', 'r')
            ->addSelect('t')
            ->addSelect('r')
            ->orderBy('a.updatedAt', 'desc')
        ;

        if ($options['targets']) {
            $qb
                ->andWhere($qb->expr()->in('a.target', ':targets'))
                ->setParameter('targets', $options['targets'])
            ;
        }

        if ($options['triggers']) {
            $qb
                ->andWhere($qb->expr()->in('a.trigger', ':triggers'))
                ->setParameter('triggers', $options['triggers'])
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

        $resolver->define('targets')
            ->default(null)
            ->allowedTypes('null', Target::class.'[]')
        ;

        $resolver->define('triggers')
            ->default(null)
            ->allowedTypes('null', Trigger::class.'[]')
        ;

        return $resolver;
    }
}
