<?php

namespace Test\Integrated;

use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Event\Dispatcher\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Test\Integrated\src\MyEvent;

class SubscribeTest extends TestCase
{
    /**
     * @throws \Iguan\Common\Data\JsonException
     */
    public function testInvoking()
    {
        $strategy = new CliTestCommunicateStrategy();
        $dispatcher = new EventDispatcher('tag', $strategy);
        $event = new MyEvent();
        $event->setToken("some.event");
        $event->setPayload(['test' => 'payload']);
        $dispatcher->dispatch($event);
        $output = $strategy->getLastRunOutput();

        $decoder = new JsonDataDecoder(true);
        $bundle = $decoder->decode($output);

        $this->assertEquals($event->pack()->asArray(), $bundle);
    }
}
