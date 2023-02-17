<?php

namespace Code202\Security\Request;

use Symfony\Component\Validator\Constraints\Positive;

class PagerRequest implements ServiceRequest
{
    #[Positive]
    public int $page = 1;

    #[Positive]
    public int $maxPerPage = 10;
}
