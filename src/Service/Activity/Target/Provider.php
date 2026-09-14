<?php

namespace Code202\Security\Service\Activity\Target;

use Code202\Security\Entity\Activity\Target;
use Code202\Security\Entity\Activity\TargetReference;
use Code202\Security\Entity\Activity\TargetUnknown;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class Provider
{
    public function __construct(
        protected EntityManagerInterface $em,
        #[AutowireIterator('code202.security.activity.target.provider')] protected iterable $providers
    ) {}

    public function get(TargetReference $reference): Target
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($reference)) {
                return $provider->get($reference);
            }
        }

        $repository = $this->em->getRepository(TargetUnknown::class);

        $res = $repository->findOneBy([]);

        if (!$res instanceof TargetUnknown) {
            $res = new TargetUnknown();
        }

        return $res;
    }

    public function findAll(TargetReference $reference): array
    {
        $res = [];

        foreach ($this->providers as $provider) {
            $res = array_merge($res, $provider->findAll($reference));
        }

        return $res;
    }
}
