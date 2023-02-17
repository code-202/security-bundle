<?php

namespace Code202\Security\Request\Account;

use Symfony\Component\Validator\Constraints\Choice;
use Code202\Security\Request\PagerRequest as BasePagerRequest;

class PagerRequest extends BasePagerRequest
{
    #[Choice(['all', 'active', 'inactive'])]
    public string $show = 'all';

    #[Choice(['name', 'date'])]
    public string $sort = 'name';
}
