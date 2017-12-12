<?php

namespace Iguan\Event\Common\Remote;

use Iguan\Common\Data\DataDecoder;
use Iguan\Common\Data\DataEncoder;
use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Common\Data\JsonDataEncoder;
use Iguan\Common\Remote\SocketStreamException;
use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Dispatcher\EventDispatchException;
use Iguan\Event\Subscriber\GlobalEventExtractor;
use Iguan\Event\Subscriber\Subject;
use Iguan\Event\Subscriber\SubjectNotifier;

/**
 * Class RemoteDispatchStrategy
 * A dispatch strategy, when event server is on remote
 * host and can be accessible via remote communication
 * using RemoteClient.
 *
 * @author Vishnevskiy Kirill
 */
class RemoteCommunicateStrategy extends CommunicateStrategy
{

    /**
     * @var RemoteClient
     */
    private $remoteClient;

    /**
     * @var DataEncoder
     */
    private $encoder;

    /**
     * @var DataDecoder
     */
    private $decoder;

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
     * @param DataDecoder $decoder a decoder to decode received payload data.
     *                    Ensure, that events are received in supported by passed decoding method.
     */
    public function __construct(RemoteClient $remoteClient, DataEncoder $encoder = null, DataDecoder $decoder = null)
    {
        $this->remoteClient = $remoteClient;

        if ($encoder === null) {
            $encoder = new JsonDataEncoder();
        }

        if ($decoder === null) {
            $decoder = new JsonDataDecoder();
        }

        $this->encoder = $encoder;
        $this->decoder = $decoder;
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
     * @throws \Iguan\Common\Data\JsonException
     */
    public final function emitEvent(EventDescriptor $descriptor)
    {
        $this->doJsonRpcCall('fireEvent', [$this->encoder->encode($descriptor)]);
    }

    /**
     * @param $method
     * @param array $params
     * @throws \Iguan\Common\Data\JsonException
     */
    private function doJsonRpcCall($method, array $params)
    {
        $rpcId = uniqid("", true);

        $jsonRpcData = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'id' => $rpcId,
            'params' => $params
        ];

        $jsonDataEncoder = new JsonDataEncoder();
        $data = $jsonDataEncoder->encode($jsonRpcData);

        try {
            $auth = $this->getAuth();
            $this->remoteClient->write($data, $auth);
        } catch (SocketStreamException $exception) {
            throw new EventDispatchException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($this->waitForAnswer) {
            $answer = $this->readAndCheckAnswer($rpcId);
            $this->onAnswerReceived($method, $answer);
        }
    }

    /**
     * Read message for socket and validate it.
     *
     * @param string $exceptedId an RPC ID, that must be in server reply.
     * @return mixed server data reply.
     * @throws \Iguan\Common\Data\JsonException
     */
    private function readAndCheckAnswer($exceptedId)
    {
        $answer = $this->remoteClient->read();
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
     * @param $method
     * @param mixed $answer server answer in 'data' JSON RPC reply field.
     */
    protected function onAnswerReceived($method, $answer)
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

    /**
     * Register new subject as an event handler.
     * It does not mean, that subject is ready to receive an
     * events. For receiving events need to subscribe in EventSubscriber.
     *
     * @param Subject $subject to register
     * @throws \Iguan\Common\Data\JsonException
     */
    public function register(Subject $subject, $sourceTag)
    {
        $way = $subject->getNotifyWay();
        $this->doJsonRpcCall('register', [$sourceTag, $way->getInfo()]);
    }

    /**
     * Cancel registration for passed subject.
     * This subject will never receive any invokes.
     *
     * @param Subject $subject to unsubscribe
     * @param $sourceTag
     * @throws \Iguan\Common\Data\JsonException
     */
    public function unRegister(Subject $subject, $sourceTag)
    {
        $way = $subject->getNotifyWay();
        $this->doJsonRpcCall('unregister', [$sourceTag, $way->getInfo()]);
    }

    /**
     * @param $sourceTag
     * @throws \Iguan\Common\Data\JsonException
     */
    public function unRegisterAll($sourceTag)
    {
        $this->doJsonRpcCall('unregisterall', [$sourceTag]);
    }

    /**
     * @param Subject $subject
     * @param $sourceTag
     * @throws \Iguan\Common\Data\EncodeDecodeException
     * @throws \Iguan\Common\Data\JsonException
     */
    public function subscribe(Subject $subject)
    {
        $way = $subject->getNotifyWay();
        $extractor = $this->getEventExtractor();
        $eventDescriptors = $extractor->extract($way);

        $this->getSubjectNotifier()->notifyMatched($subject, $eventDescriptors);
    }

    protected function getEventExtractor()
    {
        return new GlobalEventExtractor($this->getAuth(), $this->decoder);
    }

    protected function getSubjectNotifier()
    {
        return new SubjectNotifier();
    }
}
