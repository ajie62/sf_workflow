<?php

declare (strict_types=1);

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findLastArticlePublished();

        return $this->render('home/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}
