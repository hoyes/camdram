<?php
namespace Acts\CamdramInfobaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
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

    public function sideBarAction()
    {
        $repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:ArticleTag');
        $tags = $repo->findSortedWithCount();

        return $this->render('ActsCamdramInfobaseBundle:Article:side-bar.html.twig',
            ['tags' => $tags]);
    }

    public function getAction($slug)
    {
        $repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:Article');
        $article = $repo->findOneBy(['slug' => $slug]);

        return $this->view($article, 200)
            ->setTemplate('ActsCamdramInfobaseBundle:Article:view.html.twig')
            ->setTemplateVar('article')
            ;
    }
}
