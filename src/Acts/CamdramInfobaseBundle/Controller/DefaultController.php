<?php
namespace Acts\CamdramInfobaseBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * The infobase home page
     *
     * Fetches data for the word cloud and recent article lists
     */
    public function indexAction()
    {
        $tag_repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:ArticleTag');
        $article_repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:Article');

        /* Calculate the word cloud weights (a number from roughly 80% to 400%
           of default font size) */

        /* This returns an array of arrays e.g. [[tag: {name: 'xyz'}, num: 7],
            where 'num' is the number of articles linked to that tag */
        $tags = $tag_repo->findWithCountSortedByName(30);
        /* First find the min and max article count, so that tag weights can be
           scaled proportionally */
        $min = PHP_INT_MAX; $max = 0;
        foreach ($tags as &$tag) {
            if ($tag['num'] > $max) $max = $tag['num'];
            if ($tag['num'] < $min) $min = $tag['num'];
        }
        foreach ($tags as &$tag) {
            //Calculate a number from 0.0..1.0 for each tag
            $fraction = ($tag['num'] - $min) / ($max - $min);

            /* Tag counts roughly follow an inverse exponential (a few
               tags appear really often and lots of tags appear only once). The
               below counteracts this to make the distribution of tag weights
               more uniform. It then scales and offsets the output.

               This will likely need tweaking later on as more 'real' tags are
               created */
            $tag['cloud_weight'] = round(log(1 + $fraction) * 400 + 80, 2);
        }

        return $this->render('ActsCamdramInfobaseBundle:Default:index.html.twig', [
            'tags' => $tags,
            'recently_created' => $article_repo->findRecentlyCreated(5),
            'recently_updated' => $article_repo->findRecentlyUpdated(5),
        ]);
    }

    /**
     * Collects the data used by the infobase sidebar
     *
     * Depending on the current page context, this may be called passing either
     * a current tag, a current article or neither. This method uses this to
     * produce data relevant to the current context.
     *
     * This is only ever embedded in templates so the objects themselves are
     * passed in as parameters so save an extra lookup step, even though this
     * wouldn't be possible if this were called from a URL
     *
     * @param ArticleTag|null $tag      An ArticleTag from the current page context
     * @param Article|null    $article  An Article from the current page context
     */
    public function sideBarAction($tag = null, $article = null)
    {
        $article_repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:Article');
        $tag_repo = $this->getDoctrine()->getRepository('ActsCamdramInfobaseBundle:ArticleTag');

        if ($tag) {
            $related_tags = $tag_repo->findRelated($tag, 5);
        } else {
            $related_tags = [];
        }
        if ($article) {
            $related_articles = $article_repo->findRelated($article, 20);
        } else {
            $related_articles = [];
        }

        return $this->render('ActsCamdramInfobaseBundle:Default:side-bar.html.twig', [
            'related_articles' => $related_articles,
            'related_tags' => $related_tags,
            'more_tags' => $tag_repo->findWithCountSortedByCount(30),
        ]);
    }


}
