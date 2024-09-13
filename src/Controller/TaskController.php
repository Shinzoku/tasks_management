<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Restrict access to users with the 'ROLE_ADMIN' role
#[IsGranted('ROLE_ADMIN')]
// Define the base route for all task-related actions under 'admin/task'
#[Route('admin/task')]
class TaskController extends AbstractController
{
    // Route to display all tasks
    #[Route('/', name: 'app_task_index', methods: ['GET'])]
    public function index(Request $request, TaskRepository $taskRepository): Response
    {
        $page = $request->query->getInt('page', 1); // Current page, by default 1
        $limit = 10; // Number of tasks per page

        // Retrieve sort settings
        $sortField = $request->query->get('sort', 'id'); // Default sort by ID
        $sortOrder = $request->query->get('order', 'asc'); // Default ascending order

        // Using the repository method to retrieve users with paging and sorting
        $pagination = $taskRepository->findPaginatedTasks($page, $limit, $sortField, $sortOrder);

        return $this->render('task/index.html.twig', [
            'pagination' => $pagination,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
        ]);
    }

    // Route to create a new task for a specific task list
    #[Route('admin/task_list/{id}/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TaskList $taskList): Response
    {
        // Create a new Task entity
        $task = new Task();
        // Create and handle the form for creating a new task
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        
        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the initial state of the task (not completed) and associate it with a task list
            $task->setCompleted(false);
            $task->setTaskList($taskList);

            // Persist the task entity and flush changes to the database
            $entityManager->persist($task);
            $entityManager->flush();

            // Redirect to the task list show page after saving
            return $this->redirectToRoute('app_task_list_show', ['id' => $taskList->getId()], Response::HTTP_SEE_OTHER);
        }

        // Render the form for creating a new task
        return $this->render('task/new.html.twig', [
            'task' => $task,
            'task_list' => $taskList,
            'form' => $form,
        ]);
    }

    // Route to display a specific task
    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        // Render the task details page
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    // Route to edit an existing task
    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        // Create and handle the form for editing a task
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the progress of the task from the form data
            $progress = $form->get('progress')->getData();

            // If the task progress reaches 100%, mark it as completed
            if ($progress == 100) {
                $task->setCompleted(true);
                $entityManager->persist($task);
            }

            // Save changes to the database
            $entityManager->flush();

            // Redirect to the task show page after editing
            return $this->redirectToRoute('app_task_show', ['id' => $task->getId()], Response::HTTP_SEE_OTHER);
        }

        // Render the form for editing a task
        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    // Route to delete a specific task
    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        // Verify the CSRF token to ensure the deletion request is legitimate
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            // Remove the task entity and flush changes to the database
            $entityManager->remove($task);
            $entityManager->flush();
        }

        // Redirect to the task index page after deletion
        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }
}
