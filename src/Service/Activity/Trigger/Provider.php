<?php

namespace Code202\Security\Service\Activity\Trigger;

use Code202\Security\Entity\Activity\Trigger;
use Code202\Security\Entity\Activity\TriggerReference;
use Code202\Security\Entity\Activity\TriggerUnknown;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class Provider
{
    public function __construct(
        protected EntityManagerInterface $em,
        #[TaggedIterator('code202.security.activity.trigger.provider')] protected iterable $providers
    ) {}

    public function get(): Trigger
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports()) {
                return $provider->get();
            }
        }

        $repository = $this->em->getRepository(TriggerUnknown::class);

        $res = $repository->findOneBy([]);

        if (!$res) {
            $res = new TriggerUnknown();
        }

        return $res;
    }

    public function findAll(TriggerReference $reference): array
    {
        $res = [];

        foreach ($this->providers as $provider) {
            $res = array_merge($res, $provider->findAll($reference));
        }

        return $res;
    }
}
