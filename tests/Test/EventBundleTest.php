<?php

namespace Test;

use Iguan\Event\EventBundle;
use PHPUnit\Framework\TestCase;

/**
 * Class EventBundleTest
 *
 * @author Vishnevskiy Kirill
 */
class EventBundleTest extends TestCase
{
    /**
     * @expectedException \Iguan\Common\ImmutableException
     */
    public function testImmutableAfterLock() {
        $bundle = new EventBundle();
        $bundle->lock();
        $bundle->setToken('Stub!');
    }

    public function testMutableBeforeLock() {
        $bundle = new EventBundle();
        $bundle->setToken('token');
        $bundle->setPayload('payload');
        $bundle->setClass('class');

        $this->assertTrue(true);
    }
}
