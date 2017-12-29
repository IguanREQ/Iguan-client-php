<?php
namespace Test\Subscriber;

use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Event;
use Iguan\Event\Subscriber\Subject;
use Iguan\Event\Subscriber\SubjectCliNotifyWay;
use Iguan\Event\Subscriber\SubjectNotifier;
use PHPUnit\Framework\TestCase;

/**
 * Class SubjectNotifierTest
 *
 * @author Vishnevskiy Kirill
 */
class SubjectNotifierTest extends TestCase
{
    private $testDescriptors = [];

    protected function setUp()
    {
        $descriptor = new EventDescriptor();
        $descriptor->id = 0;
        $this->testDescriptors[] = $descriptor;

        $descriptor = new EventDescriptor();
        $descriptor->id = 1;
        $event = new Event();
        $event->setToken('event.entity.action');
        $descriptor->raisedEvent = $event;
        $this->testDescriptors[] = $descriptor;

        $descriptor = new EventDescriptor();
        $descriptor->id = 2;
        $event = new Event();
        $event->setToken('domain.event.entity.action');
        $descriptor->raisedEvent = $event;
        $this->testDescriptors[] = $descriptor;

        $descriptor = new EventDescriptor();
        $descriptor->id = 3;
        $event = new Event();
        $event->setToken('domain.entity.action');
        $descriptor->raisedEvent = $event;
        $this->testDescriptors[] = $descriptor;

        $descriptor = new EventDescriptor();
        $descriptor->id = 4;
        $event = new Event();
        $event->stopPropagation();
        $event->setToken('event.entity.stopped');
        $descriptor->raisedEvent = $event;
        $this->testDescriptors[] = $descriptor;

        $descriptor = new EventDescriptor();
        $descriptor->id = 5;
        $event = new Event();
        $event->setToken('domain.event');
        $descriptor->raisedEvent = $event;
        $this->testDescriptors[] = $descriptor;

        $descriptor = new EventDescriptor();
        $descriptor->id = 6;
        $event = new Event();
        $event->setToken('domain.event.entity');
        $descriptor->raisedEvent = $event;
        $this->testDescriptors[] = $descriptor;
    }

    public function testFormatMatching()
    {
        $this->runForToken('event.entity.action', [1]);
        $this->runForToken('*.entity.action', [1, 3]);
        $this->runForToken('*.*.*', [1, 3, 6]);
        $this->runForToken('#', [1, 2, 3, 5, 6]);
        $this->runForToken('domain.#', [2, 3, 5, 6]);
        $this->runForToken('domain.event.entity.action', [2]);
        $this->runForToken('domain.*.entity.action', [2]);
        $this->runForToken('INVALID', []);
    }

    private function runForToken($token, $excepted)
    {
        $notifier = new SubjectNotifier();
        $exactlySubject = new Subject($token, new SubjectCliNotifyWay(''));
        $actual = [];
        $exactlySubject->addHandler(function (EventDescriptor $descriptor) use (&$actual) {
            $actual[] = $descriptor->id;
        });

        $notifier->notifyMatched($exactlySubject, $this->testDescriptors);
        $this->assertEquals($excepted, $actual);
    }
}
