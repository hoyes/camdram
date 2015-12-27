<?php

namespace Acts\CamdramInfobaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

/**
 * ArticleRevision
 *
 * @ORM\Table(name="acts_infobase_article_revisions")
 * @ORM\Entity(repositoryClass="Acts\CamdramInfobaseBundle\Entity\ArticleRevisionRepository")
 */
class ArticleRevision extends AbstractLogEntry
{
    
}
