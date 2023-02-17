<?php

namespace Code202\Security\Entity\Activity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TargetUnknown extends Target
{
    public function getReference(): Unknown
    {
        return new Unknown();
    }
}
