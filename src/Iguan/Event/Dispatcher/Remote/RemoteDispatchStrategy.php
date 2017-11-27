<?php

namespace Iguan\Event\Dispatcher\Remote;

use Iguan\Common\Encoder\DataEncoder;
use Iguan\Common\Encoder\JsonDataDecoder;
use Iguan\Common\Encoder\JsonDataEncoder;
use Iguan\Common\Remote\SocketStreamException;
use Iguan\Event\Common\RemoteClient;
use Iguan\Event\Dispatcher\DispatchStrategy;
use Iguan\Event\Dispatcher\EventDescriptor;
use Iguan\Event\Dispatcher\EventDispatchException;

/**
 * Class RemoteDispatchStrategy
 * A dispatch strategy, when event server is on remote
 * host and can be accessible via remote communication
 * using RemoteClient.
 *
 * @author Vishnevskiy Kirill
 */
class RemoteDispatchStrategy extends DispatchStrategy
{
    /**
     * @var RemoteClient
     */
    private $dispatchClient;

    /**
     * @var DataEncoder
     */
    private $encoder;

    /**
     * @var bool
     */
    private $waitForAnswer = true;

    /**
     * RemoteDispatchStrategy constructor.
     * @param RemoteClient $dispatchClient initialized remote client,
     *                                 that provide a way to communicate with
     *                             remote event server (via raw socket, http, ...)
     * @param DataEncoder $encoder an encoder to encode payload data.
     *                    Ensure, that event server are support passed encoding method.
     */
    public function __construct(RemoteClient $dispatchClient, DataEncoder $encoder = null)
    {
        $this->dispatchClient = $dispatchClient;

        if ($encoder === null) {
            $encoder = new JsonDataEncoder();
        }

        $this->encoder = $encoder;
    }

    /**
     * Emit event descriptor to remote host using current socket
     * and encoder.
     * Encoder will be used to encode a PAYLOAD data, i.e. $descriptor.
     * Method will do a preparing data to send using JSON RPC.
     * If $this->waitForAnswer is set, method also will wait
     * for server response. It may take a time, if you no need to
     * strict mode, disable response waiting, it may save a lot time
     * for dispatching.
     *
     * @param EventDescriptor $descriptor to be emitted.
     * @throws EventDispatchException in case of communicate error.
     */
    public final function emitEvent(EventDescriptor $descriptor)
    {
        $rpcId = uniqid("", true);
        $jsonRpcData = [
            'jsonrpc' => '2.0',
            'method' => 'fireEvent',
            'id' => $rpcId,
            'params' => [$this->encoder->encode($descriptor)]
        ];

        $jsonDataEncoder = new JsonDataEncoder();
        $data = $jsonDataEncoder->encode($jsonRpcData);

        try {
            $auth = $this->getAuth();
            $this->dispatchClient->write($data, $auth);
        } catch (SocketStreamException $exception) {
            throw new EventDispatchException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($this->waitForAnswer) {
            $answer = $this->readAndCheckAnswer($rpcId);
            $this->onAnswerReceived($answer);
        }
    }

    /**
     * Read message for socket and validate it.
     *
     * @param string $exceptedId an RPC ID, that must be in server reply.
     * @return mixed server data reply.
     */
    private function readAndCheckAnswer($exceptedId)
    {
        $answer = $this->dispatchClient->read();
        if (empty($answer)) throw new EventDispatchException('Cannot read server response. Event server went away.');

        $decoder = new JsonDataDecoder();
        $answer = $decoder->decode($answer);

        //-----------------------------------------------------------v - yes, by design
        if ($answer === false || !isset($answer->id) || $answer->id != $exceptedId) {
            throw new EventDispatchException('Bad server response. Error in JSON RPC format.');
        } else if (isset($answer->error)) {
            throw new EventServerException('JSON RPC error: ' . $answer->error);
        }

        return isset($answer->data) ? $answer->data : [];
    }

    /**
     * When server answer are extracted and can be handled.
     *
     * @param mixed $answer server answer in 'data' JSON RPC reply field.
     */
    protected function onAnswerReceived($answer)
    {

    }

    /**
     * Set a behavior when an event was written.
     * If true, strategy will wait for server reply and
     * also perform answer validation. It can take a lot time.
     * If false, strategy will not read socket reply.
     *
     * @param bool $waitForAnswer
     */
    public function setWaitForAnswer($waitForAnswer)
    {
        $this->waitForAnswer = $waitForAnswer;
    }
}