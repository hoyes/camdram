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
    /**
     * @var string $objectId
     * This definition change the DB type in the super-class of 'string' to 'integer'
     *
     * @ORM\Column(name="object_id", type="integer", nullable=true)
     */
    protected $objectId;

    /**
     * Set loggedAt to "now"
     */
    public function setLoggedAt(\DateTime $loggedAt = null)
    {
        if (!$loggedAt) {
            $this->loggedAt = new \DateTime();
        }
        else {
            $this->loggedAt = $loggedAt;
        }
    }

}
