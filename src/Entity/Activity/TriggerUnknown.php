<?php

namespace Code202\Security\Entity\Activity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TriggerUnknown extends Trigger
{
    public function getReference(): Unknown
    {
        return new Unknown();
    }
}
