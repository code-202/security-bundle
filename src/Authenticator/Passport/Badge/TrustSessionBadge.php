<?php

namespace Code202\Security\Authenticator\Passport\Badge;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class TrustSessionBadge implements BadgeInterface
{
    public function isResolved(): bool
    {
        return true;
    }
}
