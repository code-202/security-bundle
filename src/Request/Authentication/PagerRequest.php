<?php

namespace Code202\Security\Request\Authentication;

use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotNull;
use Code202\Security\Entity\Account;
use Code202\Security\Request\PagerRequest as BasePagerRequest;

class PagerRequest extends BasePagerRequest
{
    #[Choice(['all', 'active', 'inactive'])]
    public string $show = 'all';

    #[NotNull]
    public Account $account;
}
