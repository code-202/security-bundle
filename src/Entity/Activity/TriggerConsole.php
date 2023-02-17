<?php

namespace Code202\Security\Entity\Activity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class TriggerConsole extends Trigger
{
    protected Console $reference;

    public function __construct(
        Console $reference
    ) {
        parent::__construct();

        $this->reference = $reference;
    }

    public function getReference(): Console
    {
        return $this->reference;
    }
}
