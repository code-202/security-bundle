<?php

namespace Code202\Security\Service\Activity\Target;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Code202\Security\Entity\Activity\Target;
use Code202\Security\Entity\Activity\TargetReference;

#[AutoconfigureTag('code202.security.activity.target.provider')]
interface ProviderInterface
{
    public function supports(TargetReference $reference): bool;

    public function get(TargetReference $reference): Target;

    public function findAll(TargetReference $reference): array;
}
