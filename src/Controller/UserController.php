<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\EmailType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\View\TwitterBootstrap5View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

// Restrict access to users with the 'ROLE_ADMIN' role
#[IsGranted('ROLE_ADMIN')]
// Define the base route for all user-related actions under 'admin/user'
#[Route('admin/user')]
class UserController extends AbstractController
{
    // Inject EmailVerifier through the constructor
    public function __construct(private EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    // Route to display all users
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        // // Render the user index page and pass all users to the template
        // return $this->render('user/index.html.twig', [
        //     'users' => $userRepository->findAll(),
        // ]);
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Nombre d'utilisateurs par page

        // Récupérer les paramètres de tri
        $sortField = $request->query->get('sort', 'id'); // Par défaut tri par ID
        $sortOrder = $request->query->get('order', 'asc'); // Par défaut ordre croissant

        // Utilisation de la méthode du repository pour récupérer les utilisateurs avec pagination et tri
        $pagination = $userRepository->findPaginatedUsers($page, $limit, $sortField, $sortOrder);

        return $this->render('user/index.html.twig', [
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
        ]);
    }

    // Route to create a new user
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // Create a new User entity
        $user = new User();
        // Create and handle the form for creating a new user
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the roles and check if password is not blank
            $roles = $form->get('roles')->getData();
            $user->setRoles($roles);
            $passwordNotBlank = $form->get('password')->getData();

            if ($passwordNotBlank === null) {
                // If null display warning message and redirect new user page
                $this->addFlash('warning', 'The password should not blank');
                return $this->redirectToRoute('app_user_new', [], Response::HTTP_SEE_OTHER);
            }
            // Set the hashed password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            // Set the initial state of the email verification (not confirmed)
            $user->setVerified(false);

            // Persist the user entity and flush changes to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@example.com', 'Admin'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Displays a success message
            $this->addFlash('success', 'The registration has been successful. The user will receive an email to confirm your email.');

            // Redirect to the user index page
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        
        // Render the new user page
        return $this->render('user/new.html.twig', [
            'form' => $form,
        ]);
    }

    // Route to display a specific user
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        // Render the task details page
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    // Route to edit an existing user
    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Create and handle the form for editing a user
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {

            // Get password data from form
            $password = $form->get('password')->getData();
            
            // Hash and set the password if it was entered
            if ($password) {
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
            }

            // Persist changes and flush to the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Display a success message and redirect to the user index page
            $this->addFlash('success', 'Profil updated with success.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the form for editing a user
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    // Route to delete a specific user
    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Verify the CSRF token to ensure the deletion request is legitimate
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            
            // Get all tasks associated with user
            $tasks = $user->getTasks();

            // Unlink all tasks associated with the user before deletion
            if (!$tasks === null) {
                foreach ($tasks as $task) {
                    $task->setUser(null);
                    $entityManager->persist($task);
                };
            }

            // Persist changes and flush to the database
            $entityManager->remove($user);
            $entityManager->flush();

        }
        // Display a success message and redirect to the user index page
        $this->addFlash('success', 'Profil deleted with success.');
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    // Route to send an email to a user
    #[Route('{id}/mail', name: 'app_user_mail', methods: ['GET', 'POST'])]
    public function mail(User $user, Request $request, MailerInterface $mailer): Response
    {
        // Create and handle the email form
        $form = $this->createForm(EmailType::class, null, [
            'email' => $user->getEmail(),
            'username' => $user->getFirstName() . ' ' . $user->getLastname(),
        ]);

        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from form
            $data = $form->getData();
            
            // If email confirmation switch is enable, send a email confirmation to the user
            if ($data['emailConfirmation'] == true) {
                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('no-reply@example.com', 'Admin'))
                        ->to($data['email'])
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

            // If not, send a custom email to the user
            } else {
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@example.com', 'Admin'))
                    ->to($data['email'])
                    ->subject($data['subject'])
                    ->text($data['message']);
    
                $mailer->send($email);
            }


            // Displays a success message on the desired page
            $this->addFlash('success', 'The user will receive the email.');

            // Redirect to the user index page
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        
        // Render the mail page
        return $this->render('user/mail.html.twig', [
            'form' => $form,
        ]);
    }
}
