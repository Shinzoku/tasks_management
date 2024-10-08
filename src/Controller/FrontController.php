<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Task;
use App\Entity\TaskList;
use App\Form\TaskType;
use App\Form\TaskListType;
use App\Form\UserType;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    // Route for the homepage
    #[Route('/', name: 'app_front')]
    public function index(): Response
    {
        // Check if the user has the 'ROLE_USER' role
        if ($this->isGranted('ROLE_USER')) {
            // If yes, render the main page
            return $this->render('front/index.html.twig');
        } else {
            // If not, redirect to login page
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    // Route for displaying a user profile
    #[Route('user/{id}', name: 'app_user_show_front', methods: ['GET'])]
    public function show(User $user): Response
    {
        // Check if the user has the 'ROLE_USER' role
        if ($this->isGranted('ROLE_USER')) {
            // Render the user's profile page
            return $this->render('front/account_show.html.twig', [
                'user' => $user,
            ]);
        } else {
            // If not logged in with the 'ROLE_USER' role, show a warning and redirect to login
            $this->addFlash('warning', 'Please login');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    // Route for editing a user profile
    #[Route('user/{id}/edit', name: 'app_user_edit_front', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Check if the user has the 'ROLE_USER' role
        if ($this->isGranted('ROLE_USER')) {
            // Create a form to edit the user profile
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);
    
            // Check if the form is submitted and valid
            if ($form->isSubmitted() && $form->isValid()) {
                // Get the new password from the form
                $password = $form->get('password')->getData();
                
                // If a password is provided, hash it and set it for the user
                if ($password) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }
                // Save the updated user profile
                $entityManager->persist($user);
                $entityManager->flush();
    
                // Add success message and redirect to the user profile page
                $this->addFlash('success', 'Profil updated with success.');
                return $this->redirectToRoute('app_user_show_front', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
            // Render the user profile edit page
            return $this->render('front/account_edit.html.twig', [
                'user' => $user,
                'form' => $form,
            ]);
        } else {
            // If not logged in with the 'ROLE_USER' role, show a warning and redirect to login
            $this->addFlash('warning', 'Please login');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    // Route for displaying the task lists
    #[Route('task_list/index', name: 'app_task_list_index_front', methods: ['GET'])]
    public function task_list_index(TaskListRepository $taskListRepository): Response
    {
        // Check if the user has the 'ROLE_USER' role
        if ($this->isGranted('ROLE_USER')) {
            // Render the page showing all task lists
            return $this->render('front/task_list_index.html.twig', [
                'task_lists' => $taskListRepository->findAll(),
            ]);
        } else {
            // If not logged in with the 'ROLE_USER' role, show a warning and redirect to login
            $this->addFlash('warning', 'Please login');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    // Route for displaying a specific task list
    #[Route('task_list/{id}', name: 'app_task_list_show_front', methods: ['GET'])]
    public function task_list_show(TaskList $taskList): Response
    {
        // Check if the user has the 'ROLE_USER' role
        if ($this->isGranted('ROLE_USER')) {
            // Get the tasks related to this task list
            $tasks = $taskList->getTasks();

            // Render the task list details page
            return $this->render('front/task_list_show.html.twig', [
                'task_list' => $taskList,
                'tasks' => $tasks,
            ]);
        } else {
            // If not logged in with the 'ROLE_USER' role, show a warning and redirect to login
            $this->addFlash('warning', 'Please login');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    // Route for displaying tasks
    #[Route('task/index', name: 'app_task_index_front', methods: ['GET'])]
    public function task_index(TaskRepository $taskRepository): Response
    {
        // Check if the user has the 'ROLE_USER' role
        if ($this->isGranted('ROLE_USER')) {
            // Get the currently logged-in user
            $user = $this->getUser();

            // Render the page showing tasks for the user
            return $this->render('front/task_index.html.twig', [
                'tasks' => $taskRepository->findById($user),
            ]);
        } else {
            // If not logged in with the 'ROLE_USER' role, show a warning and redirect to login
            $this->addFlash('warning', 'Please login');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    // Route for displaying a specific task
    #[Route('task/{id}', name: 'app_task_show_front', methods: ['GET'])]
    public function task_show(Task $task): Response
    {
        // Check if the user has the 'ROLE_USER' role
        if ($this->isGranted('ROLE_USER')) {
            // Render the task details page
            return $this->render('front/task_show.html.twig', [
                'task' => $task,
            ]);
        } else {
            // If not logged in with the 'ROLE_USER' role, show a warning and redirect to login
            $this->addFlash('warning', 'Please login');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    // Route for editing a specific task
    #[Route('task/{id}/edit', name: 'app_task_edit_front', methods: ['GET', 'POST'])]
    public function task_edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        // Check if the user has the 'ROLE_USER' role
        if ($this->isGranted('ROLE_USER')) {
            // Get the currently logged-in user
            $user = $this->getUser();
            // Create a form to edit the task
            $form = $this->createForm(TaskType::class, $task);
            $form->handleRequest($request);

            // Check if the form is submitted and valid
            if ($form->isSubmitted() && $form->isValid()) {

                // Get task progress and assign the task to the user data
                $progress = $form->get('progress')->getData();
                $chooseTask = $form->get('chooseTask')->getData();
                
                // Update task progress
                $task->setProgress($progress);

                // Mark the task as completed if progress is 100%
                if ($progress == 100) {
                    $task->setCompleted(true);
                }

                // Assign task to the user if they selected it
                if ($chooseTask == true) {
                    $task->setUser($user);
                }
                
                // Save the updated task
                $entityManager->persist($task);
                $entityManager->flush();

                // Redirect to the task details page
                return $this->redirectToRoute('app_task_show_front', ['id' => $task->getId()], Response::HTTP_SEE_OTHER);
            }

            // Render the task edit page
            return $this->render('front/task_edit.html.twig', [
                'task' => $task,
                'form' => $form,
            ]);
        } else {
            // If not logged in with the 'ROLE_USER' role, show a warning and redirect to login
            $this->addFlash('warning', 'Please login');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }
}