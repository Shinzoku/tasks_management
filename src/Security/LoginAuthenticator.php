<?php

namespace App\Security;

use App\Repository\UserRepository;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Ramsey\Uuid\Uuid;
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

    // Defining LOGIN_ROUTE as a class constant. It makes the app_login route easily reusable throughout the class
    public const LOGIN_ROUTE = 'app_login';

    // Inject UrlGeneratorInterface, UserProviderInterface, RequestStack and RouterInterface through the constructor
    public function __construct(private UrlGeneratorInterface $urlGenerator, UserProviderInterface $userProvider, RequestStack $requestStack, RouterInterface $router)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userProvider = $userProvider;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }
    
    public function authenticate(Request $request): Passport
    {
        // Extracting email, password, and CSRF token from the request payload
        $payload = $request->getPayload();
        $email = $payload->getString('email');
        $password = $payload->getString('password');
        $csrfToken = $payload->getString('_csrf_token');

        // Validation: Check if email or password is empty
        if (empty($email) || empty($password)) {
            // Throw an exception with a custom error message
            throw new CustomUserMessageAuthenticationException('Email and password must not be empty');
        }

        // Validation: Check if password length is less than 6 characters
        if (strlen($password) < 6) {
            // Throw an exception with a custom error message
            throw new CustomUserMessageAuthenticationException('Your password should be at least 6 characters');
        }

        // Validation: Check if password is longer than 15 characters
        if (strlen($password) > 15) {
            // Throw an exception with a custom error message
            throw new CustomUserMessageAuthenticationException('Your password cannot be longer than 15 characters');
        }

        // Validation: Ensure the email is in a valid format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Throw an exception with a custom error message
            throw new CustomUserMessageAuthenticationException('Invalid email format.');
        }

        // Store the last email (username) for error handling purposes
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Load the user from the email (this may require adapting the UserProvider)
        $userProvider = $this->userProvider;
        $user = $userProvider->loadUserByIdentifier($email);

        // Check if the user has verified their email
        if (!$user->isVerified()) {
            // Throw an exception with a custom error message
            throw new CustomUserMessageAuthenticationException('Please confirm your email before login');
        }

        // Return a Passport object for authentication with credentials and badges
        return new Passport(
            new UserBadge($email),                  // Represents the user identifier
            new PasswordCredentials($password),     // Holds the password credentials
            [
                new CsrfTokenBadge('authenticate', $csrfToken),     // Validates the CSRF token
                new RememberMeBadge(),                              // Enables the "Remember Me" functionality
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Get the authenticated user and their roles
        $user = $token->getUser();
        $roles = $user->getRoles();

        // Generate a unique UUID
        $uniqueId = Uuid::uuid4()->toString();
        
        // Generate the JWT
        $jwtSecretKey = $_ENV['JWT_SECRET_KEY']; // Make sure this key is in your .env
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($jwtSecretKey)
        );

        $now = new \DateTimeImmutable();
        $tokenJwt = $config->builder()
            ->issuedBy('https://127.0.0.1:8000/')  // The token issuer
            ->permittedFor('https://127.0.0.1:8000/') // Recipient of the token
            ->identifiedBy($uniqueId, true)  // A unique ID for the token
            ->issuedAt($now)  // When the token is issued
            ->expiresAt($now->modify('+1 hour'))  // Token expiration date
            ->withClaim('user_id', $user->getId())  // User ID
            ->withClaim('roles', $roles)  // Add the roles to the token
            ->getToken($config->signer(), $config->signingKey());

        // Convert JWT token to chain
        $jwt = $tokenJwt->toString();

        // Return the JWT to the frontend via session or cookies, or in the JSON response
        // For example, we can add it to a JSON response for an API Login
        return new JsonResponse([
            'message' => 'Authentication successful',
            'token' => $jwt
        ]);

        // Check if there's a previously requested path (e.g., before the login attempt)
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // If the user has the 'ROLE_ADMIN' role, redirect them to the admin dashboard or homepage
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_front'));
        
        // If the user has the 'ROLE_USER' role, redirect them to the frontend homepage
        } elseif (in_array('ROLE_USER', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('app_front'));
        }
        // If none of the roles match, default to redirecting them to the login page
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Get session from current request
        $session = $this->requestStack->getSession();

        // Add a flash message to the session to notify the user of the authentication failure
        $session->getFlashBag()->add('danger', 'Authentication failed, no account found for the provided email : ' . $exception->getMessageKey());

        // Redirect the user back to the login page
        return new RedirectResponse($this->router->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        // Generates the login URL to which the user is redirected after a failed authentication attempt
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
