<?php

namespace Code202\Security\Service\Activity\Trigger;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Code202\Security\Entity\Activity\Trigger;
use Code202\Security\Entity\Activity\TriggerReference;

#[AutoconfigureTag('code202.security.activity.trigger.provider')]
interface ProviderInterface
{
    public function supports(): bool;

    public function get(): Trigger;

    public function findAll(TriggerReference $reference): array;
}
