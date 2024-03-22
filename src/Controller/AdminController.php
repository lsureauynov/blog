<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin_index', methods: ['GET'])]
    public function index() : Response
    {
        return $this->render('admin/index.html.twig');
    }
    
    #[Route('/manage_users', name: 'admin_manage_users', methods: ['GET'])]
    public function manageUsers() : Response
    {
        return $this->render('admin/manage_users.html.twig');
    }

    #[Route('/manage_articles', name: 'admin_manage_articles', methods: ['GET'])]
    public function manageArticles() : Response
    {
        return $this->render('admin/manage_articles.html.twig');
    }

    #[Route('/manage_comments', name: 'admin_manage_comments', methods: ['GET'])]
    public function manageComments() : Response
    {
        return $this->render('admin/manage_comments.html.twig');
    }
}