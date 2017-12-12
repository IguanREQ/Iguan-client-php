<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 12.12.2017
 * Time: 23:03
 */

namespace Test\Subscriber;

use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Subscriber\Subject;
use Iguan\Event\Subscriber\SubjectCliNotifyWay;
use PHPUnit\Framework\TestCase;

class SubjectTest extends TestCase
{
    public function testNotifySubscribers()
    {
        $subject = new Subject('#', new SubjectCliNotifyWay(''));
        $isCalled = false;
        $callback = function (EventDescriptor $descriptor) use (&$isCalled) {
            TestCase::assertNotEmpty($descriptor);
            $isCalled = true;
        };

        $subject->addHandler($callback);
        $subject->notifyAll(new EventDescriptor());

        $this->assertTrue($isCalled);
    }
}
