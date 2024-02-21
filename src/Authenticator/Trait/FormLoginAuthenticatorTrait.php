<?php

namespace Code202\Security\Authenticator\Trait;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\ParameterBagUtils;

trait FormLoginAuthenticatorTrait
{
    public function supports(Request $request): ?bool
    {
        if (!parent::supports($request)) {
            return false;
        }

        if ($request->headers->get('Content-type')
            && 'application/x-www-form-urlencoded' !== $request->headers->get('Content-type')
            && !str_contains($request->headers->get('Content-type'), 'multipart/form-data')
        ) {
            return false;
        }

        return true;
    }

    protected function getCredentials(Request $request): array
    {
        $credentials = [];

        $credentials['csrf_token'] = ParameterBagUtils::getRequestParameterValue($request, $this->options['csrf_parameter']);
        $credentials['key'] = ParameterBagUtils::getParameterBagValue($request->request, $this->options['username_parameter']);

        if (!\is_string($credentials['key'])) {
            throw new BadRequestHttpException(sprintf('The key "%s" must be a string.', $this->options['username_parameter']));
        }

        if (\strlen($credentials['key']) > UserBadge::MAX_USERNAME_LENGTH) {
            throw new BadCredentialsException('Invalid key.');
        }

        return array_merge($this->getExtraCredentials($request), $credentials);
    }

    protected function getExtraCredentials(Request $request): array
    {
        return [];
    }

    protected function addExtraBadges(Passport $passport, array $credentials)
    {
        parent::addExtraBadges($passport, $credentials);

        if ($this->options['enable_csrf']) {
            $passport->addBadge(new CsrfTokenBadge($this->options['csrf_token_id'], $credentials['csrf_token']));
        }
    }
}
