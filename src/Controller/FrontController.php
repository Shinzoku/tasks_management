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
    #[Route('/', name: 'app_front')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->render('front/index.html.twig');
        } else {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('user/{id}', name: 'app_user_show_front', methods: ['GET'])]
    public function show(User $user): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->render('front/account_show.html.twig', [
                'user' => $user,
            ]);
        } else {
            $this->addFlash('warning', 'Please login');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('user/{id}/edit', name: 'app_user_edit_front', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
    
                $password = $form->get('password')->getData();
                
                if ($password) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }
    
                $entityManager->persist($user);
                $entityManager->flush();
    
                $this->addFlash('success', 'Profil updated with success.');
    
                return $this->redirectToRoute('app_user_show_front', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
    
            return $this->render('front/account_edit.html.twig', [
                'user' => $user,
                'form' => $form,
            ]);
        } else {
            $this->addFlash('warning', 'Please login');
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('task_list/index', name: 'app_task_list_index_front', methods: ['GET'])]
    public function task_list_index(TaskListRepository $taskListRepository): Response
    {
        return $this->render('front/task_list_index.html.twig', [
            'task_lists' => $taskListRepository->findAll(),
        ]);
    }

    #[Route('task_list/{id}', name: 'app_task_list_show_front', methods: ['GET'])]
    public function task_list_show(TaskList $taskList): Response
    {
        $tasks = $taskList->getTasks();

        return $this->render('front/task_list_show.html.twig', [
            'task_list' => $taskList,
            'tasks' => $tasks,
        ]);
    }

    #[Route('task/index', name: 'app_task_index_front', methods: ['GET'])]
    public function task_index(TaskRepository $taskRepository): Response
    {
        $user = $this->getUser();

        return $this->render('front/task_index.html.twig', [
            'tasks' => $taskRepository->findById($user),
        ]);
    }

    #[Route('task/{id}', name: 'app_task_show_front', methods: ['GET'])]
    public function task_show(Task $task): Response
    {
        return $this->render('front/task_show.html.twig', [
            'task' => $task,
        ]);
    }

    
    #[Route('task/{id}/edit', name: 'app_task_edit_front', methods: ['GET', 'POST'])]
    public function task_edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $progress = $form->get('progress')->getData();
            $chooseTask = $form->get('chooseTask')->getData();
            
            $task->setProgress($progress);

            if ($progress == 100) {
                $task->setCompleted(true);
            }

            if ($chooseTask == true) {
                $task->setUser($user);
            }
            
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_show_front', ['id' => $task->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/task_edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }
}