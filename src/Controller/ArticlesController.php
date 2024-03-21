<?php

namespace App\Controller;

use App\Entity\Articles;
use App\Form\ArticlesType;
use App\Repository\ArticlesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\UserRepository;
use App\Repository\CommentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/articles')]
class ArticlesController extends AbstractController
{
    private ArticlesRepository $articlesRepository;
    private CategoryRepository $categoriesRepository;
    private UserRepository $userRepository;
    private CommentsRepository $commentsRepository;
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;

    public function __construct(
        ArticlesRepository $articlesRepository,
        UserRepository $userRepository,
      CategoryRepository $categoriesRepository,
        CommentsRepository $commentsRepository,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ) {
        $this->articlesRepository = $articlesRepository;
        $this->userRepository = $userRepository;
        $this->categoryRepository = $categoriesRepository
        $this->commentsRepository = $commentsRepository;
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }


 
      #[Route('/', name: 'app_articles_index', methods: ['GET'])]
    public function index(): Response
    {
        $articles = $this->articlesRepository->findLastSixArticles();
        $lastArticle = $this->articlesRepository->findLastArticle();

        return $this->render('articles/index.html.twig', [
            'articles' => $articles,
            'lastArticle' => $lastArticle
        ]);
    }

    #[Route('/all', name: 'app_articles_all', methods: ['GET'])]
    public function all(): Response
    {
        $articles = $this->articlesRepository->findAll();

        return $this->render('articles/all.html.twig', [
            'articles' => $articles
        ]);
    }

    #[Route('/auth/new', name: 'app_articles_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $article = new Articles();
        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user = $this->getUser();
            $article->setUser($user);
            $data = $request->request->all("articles");


            if ($this->isCsrfTokenValid("articles", $data['_token'])) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }

            $currentDate = new \DateTime();
            $article->setDate(new \DateTime());

            $cover = $form->get('cover')->getData();
            if ($cover) {
                $originalFilename = pathinfo($cover->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $cover->guessExtension();

                try {
                    $cover->move(
                        $this->getParameter('covers_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // HandleException
                }

                $article->setCoverImage($newFilename);
            }

            $selectedCategories = $form->get('categories')->getData();
            foreach ($selectedCategories as $category) {
                $article->addCategory($category);
            }

            if ($form->isValid()) {
                $this->entityManager->persist($article);
                $this->entityManager->flush();

                return $this->redirectToRoute('app_articles_index',  [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('articles/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


  #[Route('/{id}', name: 'app_articles_show', methods: ['GET'])]
    public function show(Articles $article): Response
    {
        $user = $this->userRepository->find($article->getUser());


        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }


        $comments = $this->commentsRepository->findCommentsByArticle($article->getId());


       
        $categories = $this->categoriesRepository->findCategoryByArticle($article->getId());

        $categories = $article->getCategories();

        $articlesWithSameCategories = $articlesRepository->findArticlesByCategories($categories);
>

        return $this->render('articles/show.html.twig', [
            'article' => $article,
            'user' => $user,
            'comments' => $comments,
            'categories' => $categories,
            'articlesWithSameCategories' => $articlesWithSameCategories,
        ]);
    }

    #[Route('/auth/{id}/edit', name: 'app_articles_edit', methods: ['GET', 'POST'])]

    public function edit(Request $request, Articles $article): Response

        $form = $this->createForm(ArticlesType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $request->request->all("articles");
            if ($this->isCsrfTokenValid("articles", $data['_token'])) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }

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

            $selectedCategories = $form->get('categories')->getData();
            foreach ($selectedCategories as $category) {
                $article->addCategory($category);
            }

            if ($form->isValid()) {
                $this->entityManager->flush();

                return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('articles/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }


    
    #[Route('/adminauth/{id}', name: 'app_articles_delete', methods: ['POST'])]
    public function delete(Request $request, Articles $article): Response
    {
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($article);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_articles_index', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/search', name: "app_articles_search", methods: ["GET"])]
    public function searchByTitle(Request $request) : JsonResponse
    {

        $query = $request->query->get('query');

        $articles = $this->articlesRepository->findArticlesByTitle($query);


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

    public function searchResult(Request $request): Response
    {
    $query = $request->query->get('query');
    $articles = $this->articlesRepository->findArticlesByTitle($query);
    
    return $this->render('articles/search_results.html.twig', [
        'query' => $query,
        'articles' => $articles,
    ]);
    }


}
