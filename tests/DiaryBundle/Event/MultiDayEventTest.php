<?php

namespace Camdram\Tests\DiaryBundle\Event;

use Acts\DiaryBundle\Event\MultiDayEvent;
use PHPUnit\Framework\TestCase;

class MultiDayEventTest extends TestCase
{
    public function testMultiDayEvent()
    {
        $event = new MultiDayEvent();
        $event->setStartDate(new \DateTime('2014-02-01'));
        $event->setEndDate(new \DateTime('2014-02-07'));

        $this->assertEquals('1 February 2014', $event->getStartDate()->format('j F Y'));
        $this->assertEquals('7 February 2014', $event->getEndDate()->format('j F Y'));
    }
}
