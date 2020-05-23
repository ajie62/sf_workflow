<?php

declare (strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

/**
 * @Route("/admin/article")
 */
class AdminArticleController extends AbstractController
{
    /**
     * @Route("/", name="article_index", methods={"GET"})
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="article_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setAuthor($user);

            $this->em()->persist($article);
            $this->em()->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="article_show", methods={"GET"})
     */
    public function show(Article $article, Registry $registry): Response
    {
        $workflow = $registry->get($article);

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'availableTransitions' => $workflow->getEnabledTransitions($article),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="article_edit", methods={"GET", "POST"})
     *
     * @Security("is_granted('edit', article)")
     */
    public function edit(Request $request, Article $article): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em()->flush();

            return $this->redirectToRoute('article_index');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    /**
     * @Route("/{id}", name="article_delete", methods={"DELETE"})
     *
     * @Security("is_granted('delete', article)")
     */
    public function delete(Request $request, Article $article): Response
    {
        $submittedToken = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('delete-item', $submittedToken)) {
            $this->em()->remove($article);
            $this->em()->flush();
        }

        return $this->redirectToRoute('article_index');
    }

    /**
     * @Route("/{id}/change-status/{status}", name="article_change_status")
     */
    public function changeStatus(Article $article, string $status, Registry $registry): Response
    {
        $workflow = $registry->get($article);

        try {
            $workflow->apply($article, $status, [
                'time' => date('y-m-d H:i:s'),
            ]);
            $this->em()->flush();
        } catch (\LogicException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
    }

    private function em(): ObjectManager
    {
        return $this->getDoctrine()->getManager();
    }
}
