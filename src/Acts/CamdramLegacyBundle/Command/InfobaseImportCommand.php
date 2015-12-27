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
    const INFOBASE_BASE_ID = 119;

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

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This will truncate all infobase tables - continue? ', false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }
        $connection = $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $platform   = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('acts_infobase_articles', true));
        $connection->executeUpdate($platform->getTruncateTableSQL('acts_infobase_article_tags', true));
        $connection->executeUpdate($platform->getTruncateTableSQL('acts_infobase_article_tag_links', true));
        $connection->executeUpdate($platform->getTruncateTableSQL('acts_infobase_article_revisions', true));
        //Import articles
        $this->importPages(self::INFOBASE_BASE_ID, $output, "");

        //Truncate the revisions auto-created above and rewrite the history according to the v1 revisions
        $connection->executeUpdate($platform->getTruncateTableSQL('acts_infobase_article_revisions', true));
        $this->importRevisions(self::INFOBASE_BASE_ID, $output);

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
