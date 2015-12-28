<?php

namespace Acts\CamdramLegacyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Acts\CamdramInfobaseBundle\Entity\Article;
use Acts\CamdramInfobaseBundle\Entity\ArticleTag;
use Acts\CamdramInfobaseBundle\Entity\ArticleRevision;

/**
 * Class InfobaseImportCommand
 *
 */
class InfobaseImportCommand extends ContainerAwareCommand
{
    const INFOBASE_BASE_PAGE_ID = 119;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('camdram:infobase:import')
            ->setDescription('Import v1 infoase tables into v2 format')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('acts_camdram_backend.listener.timestampable')->disable();

        //First delete all existing infobase data (after getting confirmation)
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This will truncate all infobase tables - continue? ', false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }
        $this->truncateTable('acts_infobase_articles');
        $this->truncateTable('acts_infobase_article_tags');
        $this->truncateTable('acts_infobase_article_tag_links');
        $this->truncateTable('acts_infobase_article_revisions');

        /*
         * Two recursive passes of the pages table are done. The first creates
         * the articles themselves, then the second creates all the revisions
         */
        //Import articles
        $this->importPages(self::INFOBASE_BASE_PAGE_ID, $output, "");

        /* This isn't very nice but I can't think of a better way of doing it
         * and this code can eventually be deleted anyway
         *
         * When the pages are imported above, the Loggable Doctrine extension will
         * have automatically created a single revision based on current date
         * Here, we truncate these revisions, then manually rewrite the
         * revision history according to the v1 revisions
         */
        $this->truncateTable('acts_infobase_article_revisions');
        //Import the articles' revisions
        $this->importRevisions(self::INFOBASE_BASE_PAGE_ID, $output);
    }

    private function truncateTable($name)
    {
        $connection = $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $platform   = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL($name, true));
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function importPages($id, OutputInterface $output, $currentPath)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $page_repo = $em->getRepository('ActsCamdramLegacyBundle:Page');
        $article_repo = $em->getRepository('ActsCamdramInfobaseBundle:Article');

        $pages = $page_repo->findBy(['parent_id' => $id, 'ghost' => false]);

        foreach ($pages as $page) {
            $output->writeln($page->getFullTitle());
            $path = $currentPath . $page->getTitle();

            $firstRevision = $page->getRevisions()->first();
            $latestRevision = $page->getRevisions()->last();

            $article = new Article();
            $article->setName($page->getFullTitle())
                ->setLegacySlug($path)
                ->setBody($latestRevision->getText())
                ->setCreatedAt($firstRevision->getDate())
                ->setCreatedBy($firstRevision->getUser())
                ->setUpdatedAt($latestRevision->getDate())
                ->setUpdatedBy($latestRevision->getUser());

            $em->persist($article);

            foreach (explode('/', $currentPath) as $part) {
                if ($part) {
                    $article->addTag($this->fetchOrCreateTag($part));
                }
            }
            $this->importPages($page->getId(), $output, $path . "/");
        }
        $em->flush();
    }

    public function importRevisions($id, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $page_repo = $em->getRepository('ActsCamdramLegacyBundle:Page');
        $article_repo = $em->getRepository('ActsCamdramInfobaseBundle:Article');

        $pages = $page_repo->findBy(['parent_id' => $id, 'ghost' => false]);

        foreach ($pages as $page) {
            $article = $article_repo->findOneByName($page->getFullTitle());
            $version = 1;
            foreach ($page->getRevisions() as $revision) {
                $data = ['name'  => $article->getName(),
                         'body' => $revision->getText()];

                $log = new ArticleRevision();
                $log->setAction($version == 1 ? 'create' : 'update');
                $log->setVersion($version);
                $log->setObjectId($article->getId());
                $log->setObjectClass(get_class($article));
                $log->setUsername($revision->getUser()->getEmail());
                $log->setLoggedAt($revision->getDate());
                $log->setData($data);

                $em->persist($log);
                $version++;
            }

            $this->importRevisions($page->getId(), $output);
        }
        $em->flush();
    }

    protected function fetchOrCreateTag($name)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('ActsCamdramInfobaseBundle:ArticleTag');

        $tag = $repo->findOneBy(['name' => $name]);
        if (!$tag) {
            $tag = new ArticleTag();
            $tag->setName($name);
            $em->persist($tag);
            $em->flush();
        }
        return $tag;
    }
}
