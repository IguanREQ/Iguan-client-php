<?php

namespace Test\Dispatcher;

use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Common\Remote\SocketClient;
use Iguan\Event\Common\CommonAuth;
use Iguan\Event\Common\RemoteSocketClient;
use Iguan\Event\Dispatcher\EventDescriptor;
use Iguan\Event\Dispatcher\EventDispatcher;
use Iguan\Event\Dispatcher\Remote\RemoteCommunicateStrategy;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoteDispatchStrategyTest
 * @author Vishnevskiy Kirill
 */
class RemoteDispatchStrategyTest extends TestCase
{
    const MODE_ALRIGHT = 1;

    const MODE_INVALID_ANSWER = 2;

    const MODE_NO_ANSWER = 3;

    public function testComposingEmptyMessage()
    {
        $eventDescriptor = new EventDescriptor();
        $socket = new StubSocketClient();
        $strategy = new RemoteCommunicateStrategy(new RemoteSocketClient($socket), new NoDataEncoder());
        $strategy->setWaitForAnswer(false);
        $strategy->emitEvent($eventDescriptor);

        $writtenData = $socket->getWrittenData();
        //no auth, no payload
        $excepted = pack('C', 0);
        $this->assertEquals($excepted, substr($writtenData, 0, strlen($excepted)));
    }

    public function testComposingTokenAuth()
    {
        $eventDescriptor = new EventDescriptor();
        $socket = new StubSocketClient();
        $strategy = new RemoteCommunicateStrategy(new RemoteSocketClient($socket), new NoDataEncoder());
        $strategy->setWaitForAnswer(false);

        $token = 'token';
        $strategy->setAuth(new CommonAuth($token));

        $strategy->emitEvent($eventDescriptor);
        $writtenData = $socket->getWrittenData();

        $excepted = pack('C', CommonAuth::AUTH_TYPE_TOKEN) . pack('C', strlen($token)) . $token;
        $this->assertEquals($excepted, substr($writtenData, 0, strlen($excepted)));
    }

    public function testComposingTokenWithNameAuth()
    {
        $eventDescriptor = new EventDescriptor();
        $socket = new StubSocketClient();
        $strategy = new RemoteCommunicateStrategy(new RemoteSocketClient($socket), new NoDataEncoder());
        $strategy->setWaitForAnswer(false);

        $token = 'token';
        $tokenName = 'token_name';
        $strategy->setAuth(new CommonAuth($token, $tokenName));

        $strategy->emitEvent($eventDescriptor);
        $writtenData = $socket->getWrittenData();

        $excepted = pack('C', CommonAuth::AUTH_TYPE_TOKEN | CommonAuth::AUTH_TYPE_TOKEN_NAME)
            . pack('C', strlen($token)) . $token
            . pack('C', strlen($tokenName)) . $tokenName;
        $this->assertEquals($excepted, substr($writtenData, 0, strlen($excepted)));
    }

    public function testDispatchUnwrap()
    {
        $eventDescriptor = new EventDescriptor();
        $eventDescriptor->event = ['event' => 'data'];
        $eventDescriptor->dispatcher = EventDispatcher::DISPATCHER_PHP;
        $eventDescriptor->delay = 0;
        $eventDescriptor->firedAt = 1984;

        $socket = new StubSocketClient();
        $strategy = new RemoteCommunicateStrategy(new RemoteSocketClient($socket));
        $strategy->setWaitForAnswer(false);
        $strategy->emitEvent($eventDescriptor);
        $writtenData = $socket->getWrittenData();

        //skip auth byte
        $writtenData = substr($writtenData, self::MODE_ALRIGHT);
        $jsonDataDecoder = new JsonDataDecoder();
        $serialData = $jsonDataDecoder->decode($writtenData);
        $decodedDescriptor = $jsonDataDecoder->decode($serialData->params[0]);

        $this->assertEquals(json_decode(json_encode($eventDescriptor)), $decodedDescriptor);
    }


    public function testSuccessRemoteDispatching()
    {
        $port = '16987';
        $procHandle = proc_open('php "' . __DIR__ . DIRECTORY_SEPARATOR . 'success_event_server.php" ' . $port . ' ' . self::MODE_ALRIGHT, [], $pipes);
        try {
            $eventDescriptor = new EventDescriptor();
            $socketClient = new SocketClient('tcp://127.0.0.1:' . $port);
            $strategy = new RemoteCommunicateStrategy(new RemoteSocketClient($socketClient));
            $strategy->emitEvent($eventDescriptor);
            $this->assertTrue(true, 'Wow!');
        } finally {
            proc_close($procHandle);
        }
    }

    /**
     * @expectedException \Iguan\Event\Dispatcher\EventDispatchException
     * @expectedExceptionMessage Bad server response. Error in JSON RPC format.
     */
    public function testErrorRemoteDispatching()
    {
        $port = '16988';
        $procHandle = proc_open('php "' . __DIR__ . DIRECTORY_SEPARATOR . 'success_event_server.php" ' . $port . ' ' . self::MODE_INVALID_ANSWER, [], $pipes);
        try {
            $eventDescriptor = new EventDescriptor();
            $socketClient = new SocketClient('tcp://127.0.0.1:' . $port);
            $strategy = new RemoteCommunicateStrategy(new RemoteSocketClient($socketClient));
            $strategy->emitEvent($eventDescriptor);
        } finally {
            proc_close($procHandle);
        }
    }

    /**
     * @expectedException \Iguan\Event\Dispatcher\EventDispatchException
     * @expectedExceptionMessage Cannot read server response. Event server went away.
     */
    public function testNoAnswerRemoteDispatching()
    {
        $port = '16989';
        $procHandle = proc_open('php "' . __DIR__ . DIRECTORY_SEPARATOR . 'success_event_server.php" ' . $port . ' ' . self::MODE_NO_ANSWER, [], $pipes);
        try {
            $eventDescriptor = new EventDescriptor();
            $socketClient = new SocketClient('tcp://127.0.0.1:' . $port);
            $strategy = new RemoteCommunicateStrategy(new RemoteSocketClient($socketClient));
            $strategy->emitEvent($eventDescriptor);
            $socketClient->close();
        } finally {
            proc_terminate($procHandle);
        }
    }
}
