<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151227024816 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE acts_infobase_article_revisions');
        $this->addSql('CREATE TABLE acts_infobase_article_tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_918B8B1D989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acts_infobase_article (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, slug VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, legacy_slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5885DD1F989D9B62 (slug), UNIQUE INDEX UNIQ_5885DD1FB72D207A (legacy_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE acts_infobase_article_tag_links (article_id INT NOT NULL, articletag_id INT NOT NULL, INDEX IDX_86FE49667294869C (article_id), INDEX IDX_86FE4966475DFD9C (articletag_id), PRIMARY KEY(article_id, articletag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE acts_infobase_article_tag_links ADD CONSTRAINT FK_86FE49667294869C FOREIGN KEY (article_id) REFERENCES acts_infobase_article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acts_infobase_article_tag_links ADD CONSTRAINT FK_86FE4966475DFD9C FOREIGN KEY (articletag_id) REFERENCES acts_infobase_article_tags (id) ON DELETE CASCADE');
        $this->addSql('CREATE TABLE acts_infobase_article_revisions (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE acts_infobase_article_tag_links DROP FOREIGN KEY FK_86FE4966475DFD9C');
        $this->addSql('ALTER TABLE acts_infobase_article_tag_links DROP FOREIGN KEY FK_86FE49667294869C');
        $this->addSql('DROP TABLE acts_infobase_article_tags');
        $this->addSql('DROP TABLE acts_infobase_article');
        $this->addSql('DROP TABLE acts_infobase_article_tag_links');
    }
}
