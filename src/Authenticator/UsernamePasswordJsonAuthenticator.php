<?php

namespace Code202\Security\Authenticator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class UsernamePasswordJsonAuthenticator extends AbstractLoginAuthenticator
{
    use Trait\JsonLoginAuthenticatorTrait;
    use Trait\UsernamePasswordAuthenticatorTrait;

    protected function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'check_path' => '/login-by-username',
            'username_parameter' => '_username',
            'password_parameter' => 'password',
            'remember_me_parameter' => 'remember_me',
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

        try {
            $credentials['remember_me'] = filter_var($this->propertyAccessor->getValue($data, $this->options['remember_me_parameter']), FILTER_VALIDATE_BOOLEAN);
        } catch (NoSuchPropertyException $e) {
            $credentials['remember_me'] = false;
        }

        return $credentials;
    }
}
