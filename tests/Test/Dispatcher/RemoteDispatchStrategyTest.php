<?php

namespace Test\Dispatcher;

use Iguan\Common\Encoder\JsonDataDecoder;
use Iguan\Common\Encoder\JsonDataEncoder;
use Iguan\Event\Dispatcher\EventDescriptor;
use Iguan\Event\Dispatcher\EventDispatcher;
use Iguan\Event\Dispatcher\RemoteDispatchStrategy;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoteDispatchStrategyTest
 * @author Vishnevskiy Kirill
 */
class RemoteDispatchStrategyTest extends TestCase
{
    public function testComposingEmptyMessage()
    {
        $eventDescriptor = new EventDescriptor();
        $socket = new StubSocketClient();
        $strategy = new RemoteDispatchStrategy(new NoDataEncoder(), $socket);
        $strategy->emitEvent($eventDescriptor);

        $writtenData = $socket->getWrittenData();
        //no auth, no payload
        $this->assertEquals($writtenData, pack('C', 0) . "\n");
    }

    public function testComposingTokenAuth()
    {
        $eventDescriptor = new EventDescriptor();
        $socket = new StubSocketClient();
        $strategy = new RemoteDispatchStrategy(new NoDataEncoder(), $socket);

        $token = 'token';
        $strategy->setAuthToken($token);

        $strategy->emitEvent($eventDescriptor);
        $writtenData = $socket->getWrittenData();

        $this->assertEquals($writtenData, pack('C', RemoteDispatchStrategy::AUTH_TYPE_TOKEN) . pack('C', strlen($token)) . $token . "\n");
    }

    public function testComposingTokenWithNameAuth()
    {
        $eventDescriptor = new EventDescriptor();
        $socket = new StubSocketClient();
        $strategy = new RemoteDispatchStrategy(new NoDataEncoder(), $socket);

        $token = 'token';
        $tokenName = 'token_name';
        $strategy->setAuthToken($token, $tokenName);

        $strategy->emitEvent($eventDescriptor);
        $writtenData = $socket->getWrittenData();

        $this->assertEquals($writtenData,
            pack('C', RemoteDispatchStrategy::AUTH_TYPE_TOKEN | RemoteDispatchStrategy::AUTH_TYPE_TOKEN_NAME)
            . pack('C', strlen($token)) . $token
            . pack('C', strlen($tokenName)) . $tokenName
            . "\n");
    }

    public function testDispatchUnwrap()
    {
        $eventDescriptor = new EventDescriptor();
        $eventDescriptor->event = ['event' => 'data'];
        $eventDescriptor->dispatcher = EventDispatcher::DISPATCHER_PHP;
        $eventDescriptor->delay = 0;

        $eventDescriptor->firedAt = 1984;
        $socket = new StubSocketClient();
        $strategy = new RemoteDispatchStrategy(new JsonDataEncoder(), $socket);
        $strategy->emitEvent($eventDescriptor);
        $writtenData = $socket->getWrittenData();

        //skip auth byte
        $writtenData = substr($writtenData, 1);
        $serialData = (new JsonDataDecoder())->decode($writtenData);
        $decodedDescriptor = $serialData;

        $this->assertEquals(json_decode(json_encode($eventDescriptor)), $decodedDescriptor);
    }

}
