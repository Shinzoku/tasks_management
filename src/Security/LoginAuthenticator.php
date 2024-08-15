<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, UserProviderInterface $userProvider, RequestStack $requestStack, RouterInterface $router)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userProvider = $userProvider;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }
    
    public function authenticate(Request $request): Passport
    {
        $payload = $request->getPayload();
        $email = $payload->getString('email');
        $password = $payload->getString('password');
        $csrfToken = $payload->getString('_csrf_token');

        // Email and password NotBlank
        if (empty($email) || empty($password)) {
            throw new CustomUserMessageAuthenticationException('Email and password must not be empty');
        }

        // Constraint at last 6 charasters
        if (strlen($password) < 6) {
            throw new CustomUserMessageAuthenticationException('Your password should be at least 6 characters');
        }

        // Constraint cannot be longer than 15 characters
        if (strlen($password) > 15) {
            throw new CustomUserMessageAuthenticationException('Your password cannot be longer than 15 characters');
        }

        // Constraint email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new CustomUserMessageAuthenticationException('Invalid email format.');
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Load the user from the email (this may require adapting the UserProvider)
        $userProvider = $this->userProvider;
        $user = $userProvider->loadUserByIdentifier($email);

        // Check if user is verified
        if (!$user->isVerified()) {
            // Throw an exception with a custom error message
            throw new CustomUserMessageAuthenticationException('Please confirm your email before login');
        }

        // Return the Passport for authentication
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_front'));

        } elseif (in_array('ROLE_USER', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_front'));
        }
        return new RedirectResponse($this->urlGenerator->generate('app_front'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Get session from current request
        $session = $this->requestStack->getSession();

        // Add an error flash message
        $session->getFlashBag()->add('danger', 'Authentication failed, no account found for the provided email : ' . $exception->getMessageKey());

        // Redirect to login page
        return new RedirectResponse($this->router->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
