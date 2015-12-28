<?php
namespace Acts\CamdramInfobaseBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $tag_repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:ArticleTag');
        $article_repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:Article');

        return $this->render('ActsCamdramInfobaseBundle:Default:index.html.twig', [
            'tags' => $tag_repo->findWithCountSortedByName(30),
            'recently_created' => $article_repo->findRecentlyCreated(5),
            'recently_updated' => $article_repo->findRecentlyUpdated(5),
        ]);
    }

}
