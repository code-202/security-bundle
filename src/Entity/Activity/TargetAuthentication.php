<?php

declare(strict_types=1);

namespace Code202\Security\Entity\Activity;

use Code202\Security\Entity\Authentication;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class TargetAuthentication extends Target
{
    #[ORM\ManyToOne(targetEntity: Authentication::class)]
    #[Groups(['list'])]
    protected Authentication $reference;

    public function __construct(
        Authentication $reference
    ) {
        parent::__construct();

        $this->reference = $reference;
    }

    public function getReference(): Authentication
    {
        return $this->reference;
    }
}
