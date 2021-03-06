<?php

namespace Acts\CamdramSecurityBundle\Security\User;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Acts\CamdramSecurityBundle\Entity\User;
use Acts\CamdramSecurityBundle\Security\Exception\IdentityNotFoundException;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Acts\CamdramSecurityBundle\Entity\ExternalUser;

class CamdramUserProvider implements
    UserProviderInterface,
        OAuthAwareUserProviderInterface,
    AccountConnectorInterface
{
    /**
     * @var EntityManagerInterface;
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Actually loads by id...but has to comply with the Symfony interface
     *
     * @param string $username
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @throws
     */
    public function loadUserByUsername($id)
    {
        return $this->em->getRepository('ActsCamdramSecurityBundle:User')->findOneByEmail($id);
    }

    public function updateAccessToken(User $user, $service, $access_token)
    {
        $s = $user->getIdentityByServiceName($service);
        if ($s) {
            $s->loadAccessToken($access_token);
            $this->em->flush();
        }
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Acts\CamdramSecurityBundle\Entity\User';
    }

    public function mergeUsers($user1, $user2)
    {
        //Merge old camdram auth tokens
        $tokens = $this->em->getRepository('ActsCamdramBundle:Access')->findBy(array('uid' => $user2->getId()));
        foreach ($tokens as $token) {
            $token->setUid($user1->getId());
        }
        $this->em->flush();

        $tokens = $this->em->getRepository('ActsCamdramBundle:Access')->findBy(array('issuer_id' => $user2->getId()));
        foreach ($tokens as $token) {
            $token->setIssuerId($user1->getId());
        }
        $this->em->flush();

        $tokens = $this->em->getRepository('ActsCamdramBundle:Access')->findBy(array('revoke_id' => $user2->getId()));
        foreach ($tokens as $token) {
            $token->setRevokeId($user1->getId());
        }
        $this->em->flush();

        //Merge emails
        $emails = $this->em->getRepository('ActsCamdramBundle:Email')->findBy(array('user_id' => $user2->getId()));
        foreach ($emails as $email) {
            $email->setUserId($user1->getId());
        }
        $this->em->flush();

        //Merge email aliases
        $aliases = $this->em->getRepository('ActsCamdramBundle:EmailAlias')->findBy(array('user_id' => $user2->getId()));
        foreach ($aliases as $alias) {
            $alias->setUserId($user1->getId());
        }
        $this->em->flush();

        //Merge email sigs
        $sigs = $this->em->getRepository('ActsCamdramBundle:EmailSig')->findBy(array('user_id' => $user2->getId()));
        foreach ($sigs as $sig) {
            $sig->setUserId($user1->getId());
        }
        $this->em->flush();

        //Merge forum messages
        $msgs = $this->em->getRepository('ActsCamdramBundle:EmailSig')->findBy(array('user_id' => $user2->getId()));
        foreach ($msgs as $msg) {
            $msg->setUserId($user1->getId());
        }
        $this->em->flush();

        //Merge knowledge base
        $kbs = $this->em->getRepository('ActsCamdramBundle:KnowledgeBaseRevision')->findBy(array('user_id' => $user2->getId()));
        foreach ($kbs as $kb) {
            $kb->setUserId($user1->getId());
        }
        $this->em->flush();

        //Merge mailing list members
        $r = $this->em->getRepository('ActsCamdramBundle:MailingListMember');
        $members = $r->findBy(array('user_id' => $user2->getId()));
        foreach ($members as $member) {
            if ($r->findOneBy(array('list_id' => $member->getListId(), 'user_id' => $user1->getId()))) {
                $this->em->remove($member);
            } else {
                $member->setUserId($user1->getId());
            }
        }
        $this->em->flush();

        //Merge reviews
        $reviews = $this->em->getRepository('ActsCamdramBundle:Review')->findBy(array('user_id' => $user2->getId()));
        foreach ($reviews as $review) {
            $review->setUserId($user1->getId());
        }
        $this->em->flush();

        //Merge user identities
        $identities = $this->em->getRepository('ActsCamdramSecurityBundle:UserIdentity')->findBy(array('user' => $user2));
        foreach ($identities as $identity) {
            $identity->setUser($user1);
        }
        $this->em->flush();

        if ($user2->getPerson() && !$user1->getPerson()) {
            $user1->setPerson($user2->getPerson());
        }

        $this->em->remove($user2);
        $this->em->flush();
    }
    
    private function loadOrCreateExternalUser(UserResponseInterface $response)
    {
        $service = $response->getResourceOwner()->getName();
        $username = $response->getUsername();
        $external = $this->em->getRepository('ActsCamdramSecurityBundle:ExternalUser')->findOneBy(array(
            'service' => $service,
            'username' => $username
        ));
        
        if ($external) {
            $external->setToken($response->getAccessToken());
        } else {
            $external = new ExternalUser();
            $external->setService($service);
            $external->setEmail($response->getEmail());
            $external->setUsername($username);
            $external->setName($response->getRealName());
            $external->setProfilePictureUrl($response->getProfilePicture());
            $external->setToken($response->getAccessToken());
            $this->em->persist($external);
        }
        
        return $external;
    }
    
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $external = $this->loadOrCreateExternalUser($response);
        if ($user = $this->em->getRepository('ActsCamdramSecurityBundle:User')->findOneByEmail($response->getEmail())) {
            $external->setUser($user);
        }
        $this->em->flush();
        
        if (!$external->getUser()) {
            throw new AccountNotLinkedException(sprintf("User '%s' not found.", $response->getUsername()));
        }
        
        return $external->getUser();
    }
    
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected a Camdram User, but got "%s".', get_class($user)));
        }
           
        $external = $this->loadOrCreateExternalUser($response);
        $external->setUser($user);
        $this->em->flush();
    }
}
