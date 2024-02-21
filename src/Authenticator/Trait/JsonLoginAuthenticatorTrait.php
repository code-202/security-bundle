<?php

namespace Code202\Security\Authenticator\Trait;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

trait JsonLoginAuthenticatorTrait
{
    public function supports(Request $request): ?bool
    {
        if (!parent::supports($request)) {
            return false;
        }

        if ('application/json' !== $request->headers->get('Content-type')) {
            return false;
        }

        $data = json_decode($request->getContent());
        if (!$data instanceof \stdClass) {
            return false;
        }

        return true;
    }

    protected function getCredentials(Request $request): array
    {
        $data = json_decode($request->getContent());
        if (!$data instanceof \stdClass) {
            throw new BadRequestHttpException('Invalid JSON.');
        }

        $credentials = [];

        try {
            $credentials['key'] = $this->propertyAccessor->getValue($data, $this->options['username_parameter']);

            if (!\is_string($credentials['key'])) {
                throw new BadRequestHttpException(sprintf('The key "%s" must be a string.', $this->options['username_parameter']));
            }

            if (\strlen($credentials['key']) > UserBadge::MAX_USERNAME_LENGTH) {
                throw new BadCredentialsException('Invalid key.');
            }
        } catch (AccessException $e) {
            throw new BadRequestHttpException(sprintf('The key "%s" must be provided.', $this->options['username_parameter']), $e);
        }

        return array_merge($this->getExtraCredentials($data), $credentials);
    }

    protected function getExtraCredentials(\stdClass $data): array
    {
        return [];
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if (null === $this->failureHandler) {
            if (null !== $this->translator) {
                $errorMessage = $this->translator->trans($exception->getMessageKey(), $exception->getMessageData(), 'security');
            } else {
                $errorMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
            }

            return new JsonResponse(['error' => $errorMessage], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }
}
