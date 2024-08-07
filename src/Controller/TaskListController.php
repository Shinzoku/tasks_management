<?php

namespace App\Controller;

use App\Entity\TaskList;
use App\Form\TaskListType;
use App\Repository\TaskListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/task/list')]
class TaskListController extends AbstractController
{
    #[Route('/', name: 'app_task_list_index', methods: ['GET'])]
    public function index(TaskListRepository $taskListRepository): Response
    {
        return $this->render('task_list/index.html.twig', [
            'task_lists' => $taskListRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_task_list_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $taskList = new TaskList();
        $form = $this->createForm(TaskListType::class, $taskList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($taskList);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task_list/new.html.twig', [
            'task_list' => $taskList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_list_show', methods: ['GET'])]
    public function show(TaskList $taskList): Response
    {
        return $this->render('task_list/show.html.twig', [
            'task_list' => $taskList,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_list_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TaskList $taskList, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TaskListType::class, $taskList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_list_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task_list/edit.html.twig', [
            'task_list' => $taskList,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_list_delete', methods: ['POST'])]
    public function delete(Request $request, TaskList $taskList, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$taskList->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($taskList);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_list_index', [], Response::HTTP_SEE_OTHER);
    }
}
