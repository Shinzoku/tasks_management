<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\TaskList;
use App\Form\TaskListType;
use App\Repository\TaskListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Restrict access to users with the 'ROLE_ADMIN' role
#[IsGranted('ROLE_ADMIN')]
// Define the base route for all task_list-related actions under 'admin/task_list'
#[Route('admin/task_list')]
class TaskListController extends AbstractController
{
    // Route to display all task_lists
    #[Route('/', name: 'app_task_list_index', methods: ['GET'])]
    public function index(TaskListRepository $taskListRepository): Response
    {
        // Render the task_list index page and pass all task_lists to the template
        return $this->render('task_list/index.html.twig', [
            'task_lists' => $taskListRepository->findAll(),
        ]);
    }

    // Route to create a new task_list
    #[Route('/new', name: 'app_task_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new TaskList entity
        $taskList = new TaskList();
        // Create and handle the form for creating a new task_list
        $form = $this->createForm(TaskListType::class, $taskList);
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the current user
            $user = $this->getUser();
            // Set the user for task_list (the creator of the list)
            $taskList->setUser($user);
            // Persist the task_list entity and flush changes to the database
            $entityManager->persist($taskList);
            $entityManager->flush();

            // Redirect to the task_list index page after saving
            return $this->redirectToRoute('app_task_list_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the form for creating a new task_list
        return $this->render('task_list/new.html.twig', [
            'task_list' => $taskList,
            'form' => $form,
        ]);
    }

    // Route to display a specific task_list
    #[Route('/{id}', name: 'app_task_list_show', methods: ['GET'])]
    public function show(TaskList $taskList): Response
    {
        // Get the tasks associated with the task_list
        $tasks = $taskList->getTasks();

        // Render the task_list details page
        return $this->render('task_list/show.html.twig', [
            'task_list' => $taskList,
            'tasks' => $tasks,
        ]);
    }

    // Route to edit an existing task_list
    #[Route('/{id}/edit', name: 'app_task_list_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TaskList $taskList, EntityManagerInterface $entityManager): Response
    {
        // Create and handle the form for editing a task_list
        $form = $this->createForm(TaskListType::class, $taskList);
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Save changes to the database
            $entityManager->flush();

            // Redirect to the task_list index page after editing
            return $this->redirectToRoute('app_task_list_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the form for editing a task_list
        return $this->render('task_list/edit.html.twig', [
            'task_list' => $taskList,
            'form' => $form,
        ]);
    }

    // Route to delete a specific task_list
    #[Route('/{id}', name: 'app_task_list_delete', methods: ['POST'])]
    public function delete(Request $request, TaskList $taskList, EntityManagerInterface $entityManager): Response
    {
        // Verify the CSRF token to ensure the deletion request is legitimate
        if ($this->isCsrfTokenValid('delete'.$taskList->getId(), $request->getPayload()->getString('_token'))) {
            // Remove the task_list entity and flush changes to the database
            $entityManager->remove($taskList);
            $entityManager->flush();
        }

        // Redirect to the task_list index page after deletion
        return $this->redirectToRoute('app_task_list_index', [], Response::HTTP_SEE_OTHER);
    }
}
