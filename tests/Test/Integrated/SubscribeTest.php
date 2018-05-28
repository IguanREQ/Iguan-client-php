<?php

namespace Test\Integrated;

use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Event\Common\CommonAuth;
use Iguan\Event\Emitter\EventEmitter;
use Iguan\Event\Subscriber\AuthException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Exception;
use Test\Integrated\src\MyEvent;

class SubscribeTest extends TestCase
{
    /**
     * @throws \Iguan\Common\Data\JsonException
     */
    public function testInvoking()
    {
        $strategy = new CliTestCommunicateStrategy();
        $dispatcher = new EventEmitter('tag', $strategy);
        $event = new MyEvent();
        $event->setName("some.event");
        $event->setPayload(['ConfigTest' => 'payload']);
        $dispatcher->dispatch($event);
        $output = $strategy->getLastRunOutput();
        echo $output;
        $decoder = new JsonDataDecoder(true);
        $bundle = $decoder->decode($output);

        $this->assertEquals($event->pack()->asArray(), $bundle);
    }

    public function testEmptyOut()
    {
        $strategy = new CliTestCommunicateStrategy();
        $dispatcher = new EventEmitter('tag', $strategy);
        $event = new MyEvent();
        $event->setName("broken.event");
        $event->setPayload(['ConfigTest' => 'payload']);
        $dispatcher->dispatch($event);
        $output = $strategy->getLastRunOutput();

        $this->assertEmpty($output);
    }

    // TODO do verificator test
    public function _testFailedAuth()
    {
        $strategy = new CliTestCommunicateStrategy();
        $strategy->setAuth(new CommonAuth('token'));
        $dispatcher = new EventEmitter('tag', $strategy);
        $event = new MyEvent();
        $event->setName("some.event");
        $event->setPayload(['ConfigTest' => 'payload']);
        $dispatcher->dispatch($event);
        $output = $strategy->getLastRunOutput();
        $this->assertContains('Uncaught Iguan\Event\Subscriber\AuthException: Incoming event auth does not match with configured value.', $output);
    }
}
