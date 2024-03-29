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

    private ArticlesRepository $articlesRepository;
    private CommentsRepository $commentsRepository;
    private EntityManagerInterface $entityManager;


    public function __construct(
        ArticlesRepository $articlesRepository,
        CommentsRepository $commentsRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->articlesRepository = $articlesRepository;
        $this->commentsRepository = $commentsRepository;
        $this->entityManager = $entityManager;
    }


    #[Route('/{id}', name: 'app_profile_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $articles = $this->articlesRepository->findArticlesByUSer($user->getId());
        $comments = $this->commentsRepository->findCommentsByUser($user->getId());
        
        $articlesByComment = [];
        foreach ($comments as $comment) {
            $article = $comment->getArticles();
            if ($article) {
                $articlesByComment[$comment->getId()] = $article;
            }
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'articles' => $articles,
            'comments' => $comments,
            'articlesByComment' => $articlesByComment
        ]);
    }
    #[Route('/{id}/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data= $request->request->all("edit_user_");       

    
            if ($form->isValid()) {
                $this->entityManager->flush();
                return $this->redirectToRoute('app_profile_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
        }
    
        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        $data= $request->request->all("edit_user_");       

        if ($this->isCsrfTokenValid("edit_user_",$data['_token'])) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();


        return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
    }
}

