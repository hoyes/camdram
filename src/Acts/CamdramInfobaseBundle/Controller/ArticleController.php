<?php
namespace Acts\CamdramInfobaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Acts\CamdramInfobaseBundle\Form\Type\ArticleType;
use Acts\CamdramInfobaseBundle\Entity\Article;

/**
 * ArticleController
 *
 * REST controller for reading/writing Article objects
 *
 * @Rest\RouteResource("article")
 */
class ArticleController extends FOSRestController
{
    public function cgetAction()
    {
        $repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:Article');
        $articles = $repo->findAll();

        return $this->view($articles, 200)
            ->setTemplate('ActsCamdramInfobaseBundle:Article:index.html.twig')
            ->setTemplateVar('articles')
            ;
    }

    /**
     * @Rest\Get("articles/{slug}")
     * @Security("is_granted('VIEW', article)")
     */
    public function getAction(Article $article)
    {
        return $this->view($article, 200)
            ->setTemplate('ActsCamdramInfobaseBundle:Article:view.html.twig')
            ->setTemplateVar('article')
            ;
    }

    /**
     * @Security("is_granted('CREATE', 'Acts\\CamdramInfobaseBundle\\Entity\\Article')")
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(new ArticleType());

        return $this->render('ActsCamdramInfobaseBundle:Article:new.html.twig',
            ['form' => $form->createView()]);
    }

    /**
     * @Security("is_granted('CREATE', 'Acts\\CamdramInfobaseBundle\\Entity\\Article')")
     */
    public function postAction(Request $request)
    {
        $form = $this->createForm(new ArticleType(), new Article());
        $form->submit($request);

        if ($form->isValid()) {
            $article = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('get_article', ['slug' => $article->getSlug()], 201);
        }
        else {
            return $this->view($form->createView(), 400)
                ->setTemplate('ActsCamdramInfobaseBundle:Article:new.html.twig')
                ->setTemplateVar('form');
        }
    }

    /**
     * @Rest\Get("articles/{slug}/edit")
     * @Security("is_granted('EDIT', article)")
     */
    public function editAction(Request $request, Article $article)
    {
        $form = $this->createForm(new ArticleType(), $article);

        return $this->render('ActsCamdramInfobaseBundle:Article:edit.html.twig',
            ['form' => $form->createView()]);
    }

    /**
     * @Rest\Put("articles/{slug}")
     * @Security("is_granted('EDIT', article)")
     */
    public function putAction(Request $request, Article $article)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $em->getRepository('ActsCamdramInfobaseBundle:Article');
        $article = $repo->findOneBySlug($slug);

        $form = $this->createForm(new ArticleType(), $article);
        $form->submit($request);

        if ($form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('get_article', ['slug' => $article->getSlug()], 303);
        }
        else {
            return $this->view($form->createView(), 400)
                ->setTemplate('ActsCamdramInfobaseBundle:Article:edit.html.twig')
                ->setTemplateVar('form');
        }
    }
}
