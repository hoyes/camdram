<?php

namespace Acts\CamdramInfobaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Acts\CamdramBundle\Search\SearchableInterface;

/**
 * Article
 *
 * @ORM\Table(name="acts_infobase_articles")
 * @ORM\Entity(repositoryClass="Acts\CamdramInfobaseBundle\Entity\ArticleRepository")
 * @Gedmo\Loggable(logEntryClass="ArticleRevision")
 */
class Article implements SearchableInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Gedmo\Versioned
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Gedmo\Versioned
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Versioned
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by", type="integer", nullable=true)
     */
    private $createdBy;

    /**
     * @var int
     *
     * @ORM\Column(name="updated_by", type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    private $updatedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="legacy_slug", type="string", length=255, unique=true, nullable=true)
     */
    private $legacySlug;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="ArticleTag", inversedBy="articles")
     * @ORM\JoinTable(name="acts_infobase_article_tag_links")
     */
    private $tags;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Article
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return Article
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Article
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Article
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Article
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdBy
     *
     * @param integer $createdBy
     *
     * @return Article
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return integer
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param integer $updatedBy
     *
     * @return Article
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return integer
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set legacySlug
     *
     * @param string $legacySlug
     *
     * @return Article
     */
    public function setLegacySlug($legacySlug)
    {
        $this->legacySlug = $legacySlug;

        return $this;
    }

    /**
     * Get legacySlug
     *
     * @return string
     */
    public function getLegacySlug()
    {
        return $this->legacySlug;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tag
     *
     * @param \Acts\CamdramInfobaseBundle\Entity\ArticleTag $tag
     *
     * @return Article
     */
    public function addTag(\Acts\CamdramInfobaseBundle\Entity\ArticleTag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \Acts\CamdramInfobaseBundle\Entity\ArticleTag $tag
     */
    public function removeTag(\Acts\CamdramInfobaseBundle\Entity\ArticleTag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    //Methods to make articles searchable

    public function getShortName()
    {
        return '';
    }

    public function getDescription()
    {
        return $this->getBody();
    }

    public function getRank()
    {
        return $this->getIndexDate();
    }

    public function getIndexDate()
    {
        return $this->getUpdatedAt()->format('u');
    }

    public function getEntityType()
    {
        return 'article';
    }
}
