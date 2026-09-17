<?php

declare(strict_types=1);

namespace Code202\Security\Entity\Activity;

use Code202\Security\Entity\Session;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class TriggerSession extends Trigger
{
    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[Groups(['list'])]
    protected Session $reference;

    public function __construct(
        Session $reference
    ) {
        parent::__construct();

        $this->reference = $reference;
    }

    public function getReference(): Session
    {
        return $this->reference;
    }
}
