<?php
namespace Acts\CamdramSecurityBundle\EventListener;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Acts\CamdramSecurityBundle\Entity\PendingAccess;

/**
 * PendingAccessListener
 *
 * Functions triggered by events generated by the Security Bundle. These functions
 * result in information being logged by Camdram.
 *
 */
class PendingAccessListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log the he person that they have been granted access to a resource on the
     * site, pending creating an account.
     */
    public function postPersist(PendingAccess $pending_ace, LifecycleEventArgs $event)
    {
        $this->logger->info(sprintf('%s has granted access for %s to edit %s %d.', 
            $pending_ace->getIssuer()->getName(),
            $pending_ace->getEmail(),
            $pending_ace->getType(),
            $pending_ace->getRid()));
    }
}

