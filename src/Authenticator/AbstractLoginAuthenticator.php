<?php

namespace Code202\Security\Authenticator;

use Code202\Security\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractLoginAuthenticator implements InteractiveAuthenticatorInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $options;
    protected PropertyAccessorInterface $propertyAccessor;
    protected ?TranslatorInterface $translator = null;

    /**
     * @param array<string, mixed> $options
     * @param UserProviderInterface<UserInterface> $userProvider
     */
    public function __construct(
        protected HttpUtils $httpUtils,
        protected UserProviderInterface $userProvider,
        protected ?AuthenticationSuccessHandlerInterface $successHandler = null,
        protected ?AuthenticationFailureHandlerInterface $failureHandler = null,
        array $options = [],
        ?PropertyAccessorInterface $propertyAccessor = null
    ) {
        $this->options = array_merge([], $this->getDefaultOptions(), $options);
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * @return array<string, string>
     */
    protected function getDefaultOptions(): array
    {
        return [
            'check_path' => '/login_check',
        ];
    }

    public function supports(Request $request): ?bool
    {
        if (isset($this->options['check_path']) && !$this->httpUtils->checkRequestPath($request, $this->options['check_path'])) {
            return false;
        }

        return $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $credentials = $this->getCredentials($request);
        } catch (BadRequestHttpException $e) {
            $request->setRequestFormat('json');

            throw $e;
        }

        $passport = $this->buildPassport($credentials);
        $this->addExtraBadges($passport, $credentials);

        if ($this->userProvider instanceof PasswordUpgraderInterface) {
            $passport->addBadge(new PasswordUpgradeBadge($credentials['password'], $this->userProvider));
        }

        return $passport;
    }

    /**
     * @param array<string, mixed> $credentials
     */
    abstract protected function buildPassport(array $credentials): Passport;

    /**
     * @param array<string, mixed> $credentials
     */
    protected function addExtraBadges(Passport $passport, array $credentials): void {}

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new UsernamePasswordToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if (!$this->successHandler instanceof AuthenticationSuccessHandlerInterface) {
            return null; // let the original request continue
        }

        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if (!$this->failureHandler instanceof AuthenticationFailureHandlerInterface) {
            return null; // let the original request continue
        }

        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    public function isInteractive(): bool
    {
        return true;
    }

    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function getCredentials(Request $request): array;
}
