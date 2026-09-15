<?php

namespace Code202\Security\Authenticator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\Security\Http\ParameterBagUtils;

use function is_string;

class TokenByEmailFormAuthenticator extends AbstractLoginAuthenticator
{
    use Trait\FormLoginAuthenticatorTrait;
    use Trait\TokenByEmailAuthenticatorTrait;

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'check_path' => '/login-by-token',
            'username_parameter' => '_username',
            'password_parameter' => 'password',
            'enable_csrf' => false,
            'csrf_parameter' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getExtraCredentials(Request $request): array
    {
        $credentials = [];

        try {
            $credentials['password'] = ParameterBagUtils::getParameterBagValue($request->request, $this->options['password_parameter']);

            if (!is_string($credentials['password'])) {
                throw new BadRequestHttpException(sprintf('The password "%s" must be a string.', $this->options['password_parameter']));
            }
        } catch (AccessException $e) {
            throw new BadRequestHttpException(sprintf('The password "%s" must be provided.', $this->options['password_parameter']), $e);
        }

        return $credentials;
    }
}
