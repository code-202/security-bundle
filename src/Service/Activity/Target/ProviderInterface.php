<?php

declare(strict_types=1);

namespace Code202\Security\Service\Activity\Target;

use Code202\Security\Entity\Activity\Target;
use Code202\Security\Entity\Activity\TargetReference;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('code202.security.activity.target.provider')]
interface ProviderInterface
{
    public function supports(TargetReference $reference): bool;

    public function get(TargetReference $reference): Target;

    public function findAll(TargetReference $reference): array;
}
