<?php

declare(strict_types=1);

namespace Code202\Security\Request\Account;

use Code202\Security\Request\PagerRequest as BasePagerRequest;
use Symfony\Component\Validator\Constraints\Choice;

class PagerRequest extends BasePagerRequest
{
    #[Choice(['all', 'active', 'inactive'])]
    public string $show = 'all';

    #[Choice(['name', 'date'])]
    public string $sort = 'name';
}
