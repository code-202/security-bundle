<?php

namespace Code202\Security\Entity\Activity;

use Doctrine\ORM\Mapping as ORM;
use Code202\Security\Entity\Session;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class TargetSession extends Target
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
