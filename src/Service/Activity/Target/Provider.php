<?php

namespace Code202\Security\Service\Activity\Target;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Code202\Security\Entity\Activity\Target;
use Code202\Security\Entity\Activity\TargetReference;
use Code202\Security\Entity\Activity\TargetUnknown;

class Provider
{
    protected EntityManagerInterface $em;

    protected iterable $providers = [];

    public function __construct(
        EntityManagerInterface $em,
        #[TaggedIterator('code202.security.activity.target.provider')] iterable $providers
    ) {
        $this->em = $em;
        $this->providers = $providers;
    }

    public function get(TargetReference $reference): Target
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($reference)) {
                return $provider->get($reference);
            }
        }

        $repository = $this->em->getRepository(TargetUnknown::class);

        $res = $repository->findOneBy([]);

        if (!$res) {
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
