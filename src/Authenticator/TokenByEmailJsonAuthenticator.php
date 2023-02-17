<?php

namespace Code202\Security\Authenticator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\Exception\AccessException;

class TokenByEmailJsonAuthenticator extends AbstractLoginAuthenticator
{
    use Trait\JsonLoginAuthenticatorTrait;
    use Trait\TokenByEmailAuthenticatorTrait;

    protected function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'check_path' => '/login-by-token',
            'username_parameter' => '_username',
            'password_parameter' => 'password',
        ]);
    }

    protected function getExtraCredentials(\stdClass $data): array
    {
        $credentials = [];

        try {
            $credentials['password'] = $this->propertyAccessor->getValue($data, $this->options['password_parameter']);

            if (!\is_string($credentials['password'])) {
                throw new BadRequestHttpException(sprintf('The password "%s" must be a string.', $this->options['password_parameter']));
            }
        } catch (AccessException $e) {
            throw new BadRequestHttpException(sprintf('The password "%s" must be provided.', $this->options['password_parameter']), $e);
        }

        return $credentials;
    }
}
