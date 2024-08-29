<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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
                    // Si l'utilisateur a entré un nouveau mot de passe, on le hash et on l'assigne à l'utilisateur
                    $hashedPassword = $passwordHasher->hashPassword($user, $password);
                    $user->setPassword($hashedPassword);
                }
    
                $entityManager->persist($user);
                $entityManager->flush();
    
                $this->addFlash('success', 'Profil updated with success.');
    
                return $this->redirectToRoute('app_user_show_front', [], Response::HTTP_SEE_OTHER);
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
}