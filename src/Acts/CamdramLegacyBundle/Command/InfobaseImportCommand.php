<?php

namespace Acts\CamdramLegacyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Acts\CamdramInfobaseBundle\Entity\Article;
use Acts\CamdramInfobaseBundle\Entity\ArticleTag;

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
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Truncate articles table first? ', false);
        if ($helper->ask($input, $output, $question)) {
            $connection = $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection();
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $platform   = $connection->getDatabasePlatform();
            $connection->executeUpdate($platform->getTruncateTableSQL('acts_infobase_article', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('acts_infobase_article_tags', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('acts_infobase_article_tag_links', true));
            $connection->executeUpdate($platform->getTruncateTableSQL('acts_infobase_article_revisions', true));
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->importPages(self::INFOBASE_BASE_ID, $output, "");
    }

    protected function importPages($id, OutputInterface $output, $currentPath)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $page_repo = $em->getRepository('ActsCamdramLegacyBundle:Page');
        $article_repo = $em->getRepository('ActsCamdramInfobaseBundle:Article');

        $pages = $page_repo->findBy(['parent_id' => $id, 'ghost' => false]);

        foreach ($pages as $page) {
            $output->writeln($page->getFullTitle());
            $path = $currentPath . $page->getFullTitle();

            $firstRevision = $page->getRevisions()->first();
            $latestRevision = $page->getRevisions()->last();

            $article = new Article;
            $article->setName($page->getFullTitle())
                ->setLegacySlug($path)
                ->setBody($latestRevision->getText())
                ->setCreatedBy($firstRevision->getUser())
                ->setUpdatedBy($latestRevision->getUser());
;

            foreach (explode('/', $currentPath) as $part) {
                if ($part) {
                    $article->addTag($this->fetchOrCreateTag($part));
                }
            }

            $em->persist($article);

            $this->importPages($page->getId(), $output, $path . "/");
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
