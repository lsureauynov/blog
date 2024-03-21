<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Articles;
use App\Form\CommentsType;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;




#[Route('/comments')]
class CommentsController extends AbstractController
{

    private CommentsRepository $commentsRepository;
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        CommentsRepository $commentsRepository,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->commentsRepository = $commentsRepository;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/', name: 'app_comments_index', methods: ['GET'])]
    public function index(): Response
    {
        $comments = $this->commentsRepository->findAll();
        return $this->render('comments/index.html.twig', [
            'comments' => $comments
        ]);
    }

    #[Route('/auth/new', name: 'app_comments_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $comment = new Comments();
        
        $articleId = $request->query->get('articleId');
        $article = $this->entityManager->getRepository(Articles::class)->find($articleId);
        $user = $this->getUser();
    
        $comment->setArticles($article);
        $comment->setUser($user);
        $currentDate = new \DateTime();
        $comment->setDate(new \DateTime());
    
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Commentaire enregistré avec succès.');
    
            return new RedirectResponse($this->urlGenerator->generate('app_comments_new', ['articleId' => $articleId]));
        }
    
        return $this->render('comments/new.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_comments_show', methods: ['GET'])]
    public function show(Comments $comment): Response
    {
        return $this->render('comments/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/auth/{id}/edit', name: 'app_comments_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comments $comment): Response
    {
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);
        $articleId = $comment->getArticles()->getId();

    
    
        if ($form->isSubmitted()) {
            $data= $request->request->all("comments");       

            if ($this->isCsrfTokenValid("comments",$data['_token'])) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }
    
            if ($form->isValid()) {
                $this->entityManager->flush();

    
                return $this->redirectToRoute('app_articles_show', ['id' => $articleId]);
            }
        }
    
        return $this->render('comments/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/adminauth/{id}', name: 'app_comments_delete', methods: ['POST'])]
    public function delete(Request $request, Comments $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();
        }
        $articleId = $comment->getArticles()->getId();

        return $this->redirectToRoute('app_articles_show', ['id' => $articleId]);
}
    



        #[Route('/article/{id}', name: 'app_comments_by_article', methods: ['GET'])]
        public function getByArticle(Articles $articles): Response
        {
            $comments = $this->commentsRepository->findCommentsByArticle($articles->getId());
    
            return $this->render('comments/showByArticles.html.twig', [
                'comments' => $comments,
            ]);
        }
    
    
}
