<?php

namespace Code202\Security\Service\Activity\Trigger;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Code202\Security\Entity\Activity\Trigger;
use Code202\Security\Entity\Activity\TriggerReference;
use Code202\Security\Entity\Activity\TriggerUnknown;

class Provider
{
    protected EntityManagerInterface $em;

    protected iterable $providers = [];

    public function __construct(
        EntityManagerInterface $em,
        #[TaggedIterator('code202.security.activity.trigger.provider')] iterable $providers
    ) {
        $this->em = $em;
        $this->providers = $providers;
    }

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
