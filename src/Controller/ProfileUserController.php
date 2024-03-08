<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ArticlesRepository;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('auth/user')]

class ProfileUserController extends AbstractController 
{
    #[Route('/{id}', name:'app_profile_show' , methods: ['GET'])]
    public function show(User $user, ArticlesRepository $articlesRepository, CommentsRepository $commentsRepository): Response
    {
        $articles = $articlesRepository->findArticlesByUSer($user->getId());
        $comments = $commentsRepository->findCommentsByUser($user->getId());
        return $this->render('profile/show.html.twig', [
            'user'=>$user,
            'articles' => $articles,
            'comments' => $comments
        ]);
    }

    #[Route('/{id}/edit', name: 'app_profile_edit', methods: ['GET','POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response 
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); 

            return $this->redirectToRoute('app_profile_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user, 
            'form' => $form,
        ]);

    }

    #[Route('/{id}', name:'app_profile_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('/login', [], Response::HTTP_SEE_OTHER);
    }


}