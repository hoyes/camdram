<?php
namespace Acts\CamdramInfobaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Rest\RouteResource("tag")
 */
class TagController extends FOSRestController
{

    public function getAction($slug)
    {
        $repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:ArticleTag');
        $tag = $repo->findOneBy(['slug' => $slug]);

        return $this->view($tag, 200)
            ->setTemplate('ActsCamdramInfobaseBundle:Tag:index.html.twig')
            ->setTemplateVar('tag')
            ;
    }
}
