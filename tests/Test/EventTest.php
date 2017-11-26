<?php

namespace Test;

use Iguan\Event\Event;
use PHPUnit\Framework\TestCase;

/**
 * Class EventTest
 *
 * @author Vishnevskiy Kirill
 */
class EventTest extends TestCase
{

    /**
     * @expectedException \Iguan\Common\ImmutableException
     */
    public function testLockAfterGettingBundle() {
        $event = new Event();
        $event->pack()->setClass('Stub!');
    }

    public function testUnpacking() {
        $event_bundle = new \Iguan\Event\EventBundle();
        $event_bundle->setPayload('payload');
        $event_bundle->setToken('token');
        $event = new Event($event_bundle);
        $this->assertEquals($event->getToken(), $event_bundle->getToken(), 'Event token are not the same as in EventBundle.');
        $this->assertEquals($event->getPayload(), $event_bundle->getPayload(), 'Event payload are not the same as in EventBundle.');
    }

    public function testStopPropagation() {
        $event = new Event();
        $event->stopPropagation();
        $this->assertTrue($event->isPrevented(), 'Event does not stopped after calling Event::stopPropagation().');
    }
}
