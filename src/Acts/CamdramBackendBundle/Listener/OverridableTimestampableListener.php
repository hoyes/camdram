<?php
namespace Acts\CamdramBackendBundle\Listener;

use Doctrine\Common\EventArgs;
use Gedmo\Loggable\LoggableListener;

class OverridableTimestampableListener extends LoggableListener
{
    private $disabled = false;

    public function disable()
    {
        $this->disabled = true;
    }

    public function enable()
    {
        $this->disabled = false;
    }

    public function onFlush(EventArgs $args)
    {
        if (!$this->disabled) {
            parent::onFlush($args);
        }
    }

}
