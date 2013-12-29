<?php
namespace Acts\CamdramApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * API Applications
 *
 * @ORM\Table(name="acts_api_apps")
 * @ORM\Entity(repositoryClass="ExternalAppRepository")
 */
class ExternalApp extends BaseClient
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    protected $app_type;

    /**
     * @ORM\ManyToOne(targetEntity="Acts\CamdramBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Acts\CamdramBundle\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    protected $organisation;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $is_admin;

    /**
     * @var string
     * @ORM\Column(type="string", length=1024)
     */
    protected $website;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @ORM\ManyToMany(targetEntity="Acts\CamdramBundle\Entity\User", inversedBy="apps")
     * @ORM\JoinTable(name="acts_api_authorisations")
     */
    private $users;

    public function __construct()
    {
        parent::__construct();
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set is_admin
     *
     * @param boolean $isAdmin
     * @return ApiApp
     */
    public function setIsAdmin()
    {
        $this->is_admin = true;
        $this->user = null;
        $this->organisation = null;
    
        return $this;
    }

    /**
     * Get is_admin
     *
     * @return boolean 
     */
    public function getIsAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Set user
     *
     * @param \Acts\CamdramBundle\Entity\User $user
     * @return ApiApp
     */
    public function setUser(\Acts\CamdramBundle\Entity\User $user)
    {
        $this->user = $user;
        $this->organisation = null;
        $this->is_admin = false;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Acts\CamdramBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set organisation
     *
     * @param \Acts\CamdramBundle\Entity\Organisation $organisation
     * @return ApiApp
     */
    public function setOrganisation(\Acts\CamdramBundle\Entity\Organisation $organisation)
    {
        $this->organisation = $organisation;
        $this->user = null;
        $this->is_admin = false;
    
        return $this;
    }

    /**
     * Get organisation
     *
     * @return \Acts\CamdramBundle\Entity\Organisation 
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ExternalApp
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
     * Set description
     *
     * @param string $description
     * @return ExternalApp
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set app_type
     *
     * @param string $appType
     * @return ExternalApp
     */
    public function setAppType($appType)
    {
        $this->app_type = $appType;

        switch ($appType) {
            case 'server':
                $this->setAllowedGrantTypes(array('client_credentials'));
                break;
            default:
                $this->setAllowedGrantTypes(array('token', 'authorization_code', 'client_credentials'));
                break;
        }

        return $this;
    }

    /**
     * Get app_type
     *
     * @return string 
     */
    public function getAppType()
    {
        return $this->app_type;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return ExternalApp
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    
        return $this;
    }

    /**
     * Get website
     *
     * @return string 
     */
    public function getWebsite()
    {
        return $this->website;
    }

    public function getRedirectUrisString()
    {
        return implode("\r\n",$this->getRedirectUris());
    }

    public function setRedirectUrisString($redirect_uris)
    {
        $this->setRedirectUris(preg_split('/[\r\n,]+/',$redirect_uris));
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return ExternalApp
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return ExternalApp
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }


    /**
     * Add users
     *
     * @param \Acts\CamdramBundle\Entity\User $users
     * @return ExternalApp
     */
    public function addUser(\Acts\CamdramBundle\Entity\User $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param \Acts\CamdramBundle\Entity\User $users
     */
    public function removeUser(\Acts\CamdramBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}