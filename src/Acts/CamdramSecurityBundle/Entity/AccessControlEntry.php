<?php

namespace Acts\CamdramSecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccessControlEntry
 *
 * @ORM\Table(name="acts_access")
 * @ORM\Entity(repositoryClass="AccessControlEntryRepository")
 */
class AccessControlEntry
{
    const LEVEL_FULL_ADMIN = -1;
    const LEVEL_ADMIN = -2;
    const LEVEL_CONTENT_ADMIN = -3;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="rid", type="integer")
     */
    private $entityId;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="string", length=20)
     */
    private $type;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="aces")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="uid", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     * })
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(name="uid", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="ace_grants")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="issuerid", referencedColumnName="id")
     * })
     */
    private $grantedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationdate", type="date", nullable=false)
     */
    private $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="revokeid", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     * })
     */
    private $revokedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="revokedate", type="date", nullable=true)
     */
    private $revokedAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="contact", type="boolean")
     */
    private $contact = 0;

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
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return AccessControlEntry
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set granted_at
     *
     * @param \DateTime $grantedAt
     *
     * @return AccessControlEntry
     */
    public function setGrantedAt($grantedAt)
    {
        $this->grantedAt = $grantedAt;

        return $this;
    }

    /**
     * Get granted_at
     *
     * @return \DateTime
     */
    public function getGrantedAt()
    {
        return $this->grantedAt;
    }

    /**
     * Set revoked_at
     *
     * @param \DateTime $revokedAt
     *
     * @return AccessControlEntry
     */
    public function setRevokedAt($revokedAt)
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    /**
     * Get revoked_at
     *
     * @return \DateTime
     */
    public function getRevokedAt()
    {
        return $this->revokedAt;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return AccessControlEntry
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set granted_by
     *
     * @param User $grantedBy
     *
     * @return AccessControlEntry
     */
    public function setGrantedBy(User $grantedBy = null)
    {
        $this->grantedBy = $grantedBy;

        return $this;
    }

    /**
     * Get granted_by
     *
     * @return User
     */
    public function getGrantedBy()
    {
        return $this->grantedBy;
    }

    /**
     * Set revoker
     *
     * @param User $revoker
     *
     * @return AccessControlEntry
     */
    public function setRevokedBy(User $revoker = null)
    {
        $this->revokedBy = $revoker;

        return $this;
    }

    /**
     * Get revoker
     *
     * @return User
     */
    public function getRevokedBy()
    {
        return $this->revokedBy;
    }

    /**
     * Set user_id
     *
     * @param int $userId
     *
     * @return AccessControlEntry
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set type
     *
     * @param int $type
     *
     * @return AccessControlEntry
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set entity_id
     *
     * @param int $entityId
     *
     * @return AccessControlEntry
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entity_id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set contact
     *
     * @param bool $contact
     *
     * @return AccessControlEntry
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return bool
     */
    public function getContact()
    {
        return $this->contact;
    }
}
