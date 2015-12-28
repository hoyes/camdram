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
     * This definition changes the DB type from 'string' (as defined in the
     * super-class) to 'integer'
     *
     * @ORM\Column(name="object_id", type="integer", nullable=true)
     */
    protected $objectId;

    /**
     * The implementation in the parent class always sets loggedAt to the
     * current date/time, but when importing data from v1 tables we need to be
     * able to specify the loggedAt date explcitly
     *
     * @param \DateTime $loggedAt  The logged time. If null, the current time is
     *                             used
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
