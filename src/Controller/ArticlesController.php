<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\ArticlesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/articles')]
class ArticlesController extends AbstractController
{


    #[Route('/', name: 'app_articles_index', methods: ['GET'])]
    public function index(ArticlesRepository $articlesRepository): Response
    {
        return $this->render('articles/index.html.twig', [
            'articles' => $articlesRepository->findLastSixArticles(),
            'lastArticle' => $articlesRepository->findLastArticle()
        ]);
    }

    #[Route('/all', name: 'app_articles_all', methods: ['GET'])]
    public function all(ArticlesRepository $articlesRepository): Response
    {
        return $this->render('articles/all.html.twig', [
            'articles' => $articlesRepository->findAll()
        ]);
    }

    #[Route('/auth/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, CsrfTokenManagerInterface $csrfTokenManager, Security $security): Response

    {

        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            $user = $security->getUser();
            $article->setUser($user);
            $data = $request->request->all("articles");


            if ($this->isCsrfTokenValid("articles", $data['_token'])) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }

            $currentDate = new \DateTime();
            $article->setDate($currentDate);

            $cover = $form->get('cover')->getData();
            if ($cover) {
                $originalFilename = pathinfo($cover->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $cover->guessExtension();

                try {
                    $cover->move(
                        $this->getParameter('covers_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $article->setCoverImage($newFilename);
            }

            if ($form->isValid()) {
                $entityManager->persist($article);
                $entityManager->flush();
    
                return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
            }
        }
    
        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_articles_show', methods: ['GET'])]
    public function show(Articles $article): Response
    {

        return $this->render('articles/show.html.twig', [
            'article' => $article,
        ]);
    }
    #[Route('/auth/{id}/edit', name: 'app_articles_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Articles $article, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {

            $data = $request->request->all("articles");

            if ($this->isCsrfTokenValid("articles", $data['_token'])) {

                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }
    
            if ($form->isValid()) {
                $entityManager->flush();
    
                return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
            }
        }
    
        return $this->render('articles/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/adminauth/{id}', name: 'app_articles_delete', methods: ['POST'])]
    public function delete(Request $request, Articles $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
    }  

    #[Route('/search', name: "app_articles_search", methods: ["GET"])]
    public function searchByTitle(Request $request, ArticlesRepository $articlesRepository)
    {
        
        $query = $request->query->get('query');

        $articles = $articlesRepository->findArticlesByTitle($query);
    
        $jsonData = [];
        foreach ($articles as $article) {
            $jsonData[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
            ];
        }
    
        return new JsonResponse($jsonData);
    }

    #[Route('/search_results', name: 'app_search_result', methods: ['GET'])]
public function searchResult(Request $request, ArticlesRepository $articlesRepository): Response
{
    $query = $request->query->get('query');
    $articles = $articlesRepository->findArticlesByTitle($query);
    
    return $this->render('articles/search_results.html.twig', [
        'query' => $query,
        'articles' => $articles,
    ]);
}

   
}
