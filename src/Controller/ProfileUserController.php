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
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\Exception\InvalidCsrfTokenException;

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
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response 
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request); 
    
        if ($form->isSubmitted()) {
            $token = new CsrfToken('edit_user_' . $user->getId(), $request->request->get('_token'));
            
            if (!$csrfTokenManager->isTokenValid($token)) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }
    
            if ($form->isValid()) {
                $entityManager->flush(); 
                return $this->redirectToRoute('app_profile_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
        }
    
        return $this->render('profile/edit.html.twig', [
            'user' => $user, 
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name:'app_profile_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = new CsrfToken('delete_user_' . $user->getId(), $request->request->get('_token'));
        
        if (!$csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }
    
        $entityManager->remove($user);
        $entityManager->flush();
    
        return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
    }

}