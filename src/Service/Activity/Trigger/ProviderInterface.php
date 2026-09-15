<?php

declare(strict_types=1);

namespace Code202\Security\Service\Activity\Trigger;

use Code202\Security\Entity\Activity\Trigger;
use Code202\Security\Entity\Activity\TriggerReference;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('code202.security.activity.trigger.provider')]
interface ProviderInterface
{
    public function supports(): bool;

    public function get(): Trigger;

    /** @return Trigger[] */
    public function findAll(TriggerReference $reference): array;
}
