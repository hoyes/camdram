<?php

namespace Acts\CamdramSecurityBundle\EventListener;

use Acts\CamdramSecurityBundle\Entity\User;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * LastLoginTimeListener
 *
 * Updates the 'last_login_at' field of the user every time he/she logs in
 */
class LastLoginTimeListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onAuthenticationSuccess',
        ];
    }

    /**
     * Updates the 'last_login_at' field of the user
     */
    public function onAuthenticationSuccess(InteractiveLoginEvent $event)
    {
        $now = new \DateTime;
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();
        if ($user instanceof User) {
            $user->setLastLoginAt($now);

            if ($token instanceof UsernamePasswordToken)
            {
                $user->setLastPasswordLoginAt($now);
            }
            else if ($token instanceof OAuthToken)
            {
                if ($externalUser = $user->getExternalUserByService($token->getResourceOwnerName()))
                {
                    $externalUser->setLastLoginAt($now);
                }
            }

            $this->entityManager->flush();
        }
    }
}
