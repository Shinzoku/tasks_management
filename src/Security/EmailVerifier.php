<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    // Inject VerifyEmailHelperInterface, MailerInterface and EntityManagerInterface through the constructor
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        // Generate a signed URL for email verification
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,      // The route name to verify the email (e.g., 'app_verify_email')
            (string) $user->getId(),    // The unique ID of the user (as a string)
            $user->getEmail(),          // The user's email address
            ['id' => $user->getId()]    // Include user ID as parameter in URL
        );

        // Retrieve the existing context of the email
        $context = $email->getContext();

        // Add the signed URL and expiration details to the email context
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        // Set the modified context back to the email
        $email->context($context);

        // Send the email using the mailer service
        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, (string) $user->getId(), $user->getEmail());

        // Set email confirmation (is verified)
        $user->setVerified(true);

        // Persist and flush the changes to the data base
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
