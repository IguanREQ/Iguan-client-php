<?php

namespace Test\Subscriber;


use Iguan\Event\Subscriber\EventSubscriber;
use Iguan\Event\Subscriber\Guard\SubscriptionFileGuard;
use Iguan\Event\Subscriber\Subject;
use Iguan\Event\Subscriber\SubjectNotifyWay;
use PHPUnit\Framework\TestCase;

class EventSubscriberTest extends TestCase
{
    private $lockFilesLocation = __DIR__ . DIRECTORY_SEPARATOR . 'lock';

    public function testGuardUsage()
    {
        $strategy = new RegisterStubCommunicateStrategy();
        $subscriber = new EventSubscriber('tag', $strategy);

        $guard = new SubscriptionFileGuard('1.0', $this->lockFilesLocation);
        $subscriber->setGuard($guard);
        $testSubject = new Subject('some.token', SubjectNotifyWay::cli(__FILE__));
        $subscriber->subscribe($testSubject);
        $this->assertEquals(1, $strategy->getRegisterCount());

        $subscriber->subscribe($testSubject);
        $this->assertEquals(1, $strategy->getRegisterCount());

        $testSubject = new Subject('another.token', SubjectNotifyWay::cli(__FILE__));
        $subscriber->subscribe($testSubject);
        $this->assertEquals(2, $strategy->getRegisterCount());

        $testSubject = new Subject('another.token', SubjectNotifyWay::cli(__FILE__ . '/next'));
        $subscriber->subscribe($testSubject);
        $this->assertEquals(3, $strategy->getRegisterCount());

        $guard = new SubscriptionFileGuard('1.1', $this->lockFilesLocation);
        $subscriber->setGuard($guard);
        $testSubject = new Subject('another.token', SubjectNotifyWay::cli(__FILE__ . '/next'));
        $subscriber->subscribe($testSubject);
        $this->assertEquals(4, $strategy->getRegisterCount());

        $files = scandir($this->lockFilesLocation);
        foreach ($files as $file) {
            if (preg_match(preg_quote("/iguan_sl_tag_1.0") . '_.*\.lock/', $file, $matches)) {
                $this->fail('Old files are not cleared.');
            }
        }
    }

    protected function setUp()
    {
        mkdir($this->lockFilesLocation);
    }

    protected function tearDown()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec(sprintf("rd /s /q %s", escapeshellarg($this->lockFilesLocation)));
        } else {
            exec(sprintf("rm -rf %s", escapeshellarg($this->lockFilesLocation)));
        }
    }
}
