<?php

namespace Code202\Security\Authenticator;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\ParameterBagUtils;

class UsernamePasswordFormAuthenticator extends AbstractLoginAuthenticator implements AuthenticationEntryPointInterface
{
    use Trait\FormLoginAuthenticatorTrait;
    use Trait\UsernamePasswordAuthenticatorTrait;

    protected function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'check_path' => '/login-by-username',
            'login_path' => '/login',
            'username_parameter' => '_username',
            'password_parameter' => 'password',
            'remember_me_parameter' => 'remember_me',
            'enable_csrf' => false,
            'csrf_parameter' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
        ]);
    }

    protected function getExtraCredentials(Request $request): array
    {
        $credentials = [];

        try {
            $credentials['password'] = ParameterBagUtils::getParameterBagValue($request->request, $this->options['password_parameter']);

            if (!\is_string($credentials['password'])) {
                throw new BadRequestHttpException(sprintf('The password "%s" must be a string.', $this->options['password_parameter']));
            }
        } catch (AccessException $e) {
            throw new BadRequestHttpException(sprintf('The password "%s" must be provided.', $this->options['password_parameter']), $e);
        }

        try {
            $credentials['remember_me'] = filter_var(ParameterBagUtils::getParameterBagValue($request->request, $this->options['remember_me_parameter']), FILTER_VALIDATE_BOOLEAN);
        } catch (NoSuchPropertyException $e) {
            $credentials['remember_me'] = false;
        }

        return $credentials;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $url = $this->httpUtils->generateUri($request, $this->options['login_path']);

        return new RedirectResponse($url);
    }
}
