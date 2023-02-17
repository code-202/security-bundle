<?php

namespace Code202\Security\Entity\Activity;

use Doctrine\ORM\Mapping as ORM;
use Code202\Security\Entity\Account;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class TargetAccount extends Target
{
    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[Groups(['list'])]
    protected Account $reference;

    public function __construct(
        Account $reference
    ) {
        parent::__construct();

        $this->reference = $reference;
    }

    public function getReference(): Account
    {
        return $this->reference;
    }
}
