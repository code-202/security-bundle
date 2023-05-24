<?php

namespace Code202\Security\Request\Session;

use Symfony\Component\Validator\Constraints\Choice;
use Code202\Security\Request\PagerRequest as BasePagerRequest;

class PagerRequest extends BasePagerRequest
{
    #[Choice(['all', 'active', 'inactive'])]
    public string $show = 'all';

    public ?string $search = '';
}
