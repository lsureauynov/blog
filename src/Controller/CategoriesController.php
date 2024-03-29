<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\Exception\InvalidCsrfTokenException;

#[Route('/categories')]
class CategoriesController extends AbstractController
{
    private CategoriesRepository $categoriesRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(CategoriesRepository $categoriesRepository, EntityManagerInterface $entityManager) {
        $this->categoriesRepository = $categoriesRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_categories_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('categories/index.html.twig', [
            'categories' => $this->categoriesRepository->findAll(),
        ]);
    }

    #[Route('/admin/new', name: 'app_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $category = new Categories();
        $form = $this->createForm(CategoriesType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $request->request->all("categories");

            if ($this->isCsrfTokenValid("categories", $data['_token'])) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }

            if ($form->isValid()) {
                $this->entityManager->persist($category);
                $this->entityManager->flush();

                return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('categories/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_categories_show', methods: ['GET'])]
    public function show(Categories $category): Response
    {
        return $this->render('categories/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/admin/{id}/edit', name: 'app_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categories $category, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $form = $this->createForm(CategoriesType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $request->request->all("categories");

            if ($this->isCsrfTokenValid("categories", $data['_token'])) {

                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }

            if ($form->isValid()) {
                $this->entityManager->flush();

                return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('categories/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/{id}', name: 'app_categories_delete', methods: ['POST'])]
    public function delete(Request $request, Categories $category): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
    }
}
