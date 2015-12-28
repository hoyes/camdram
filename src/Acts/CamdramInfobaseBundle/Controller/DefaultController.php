<?php
namespace Acts\CamdramInfobaseBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $tag_repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:ArticleTag');
        $article_repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:Article');

        $tags = $tag_repo->findWithCountSortedByName(30);
        //Find min and max count
        $min = PHP_INT_MAX; $max = 0;
        foreach ($tags as &$tag) {
            if ($tag['num_articles'] > $max) $max = $tag['num_articles'];
            if ($tag['num_articles'] < $min) $min = $tag['num_articles'];
        }
        foreach ($tags as &$tag) {
            $tag['cloud_weight'] = round(log(1 + ($tag['num_articles'] - $min) / ($max - $min)) * 400 + 80, 2);
        }

        return $this->render('ActsCamdramInfobaseBundle:Default:index.html.twig', [
            'tags' => $tags,
            'recently_created' => $article_repo->findRecentlyCreated(5),
            'recently_updated' => $article_repo->findRecentlyUpdated(5),
        ]);
    }

}
