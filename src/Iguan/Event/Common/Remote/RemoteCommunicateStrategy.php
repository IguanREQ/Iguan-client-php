<?php

namespace Iguan\Event\Common\Remote;

use Iguan\Common\Data\DataDecoder;
use Iguan\Common\Data\DataEncoder;
use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Common\Data\JsonDataEncoder;
use Iguan\Common\Data\JsonException;
use Iguan\Common\Remote\SocketStreamException;
use Iguan\Event\Common\CommunicateException;
use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Subscriber\GlobalEventExtractor;
use Iguan\Event\Subscriber\Subject;
use Iguan\Event\Subscriber\Verificator\SkipVerificator;
use Iguan\Event\Subscriber\Verificator\Verificator;

/**
 * Class RemoteDispatchStrategy
 * A strategy, when event server is on remote
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
     * @var Verificator
     */
    private $verificator;

    /**
     * @var bool
     */
    private $waitForAnswer = true;



    /**
     * RemoteDispatchStrategy constructor.
     * @param RemoteClient $remoteClient initialized remote client,
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
     * A wrapper over doJsonRpcCall for preventing bubbling raw JsonException.
     * @param $method
     * @param array $params
     * @throws CommunicateException
     */
    private function doSafeJsonRpcCall($method, array $params)
    {
        try {
            $this->doJsonRpcCall($method, $params);
        } catch (JsonException $e) {
            throw new CommunicateException('Some error occurred during JSON operations.', $e->getCode(), $e);
        }
    }

    /**
     * Method will do a JSON RPC call to remote using current client.
     *
     * If $this->waitForAnswer is set, method also will wait
     * for server response. It may take a time, if you no need to
     * strict mode, disable response waiting, it may save a lot time
     * for dispatching, but improve reliability.
     *
     * @param string $method be invoking on remote
     * @param array $params a method signature params
     *
     * @throws \Iguan\Common\Data\JsonException
     * @throws RpcCallException if call failed
     */
    private function doJsonRpcCall($method, array $params)
    {
        $rpcId = uniqid("", true);

        $jsonRpcData = [
            'method' => $method,
            'id' => $rpcId,
            'params' => [$params]
        ];

        $jsonDataEncoder = new JsonDataEncoder();
        $data = $jsonDataEncoder->encode($jsonRpcData);

        try {
            $auth = $this->getAuth();
            $this->remoteClient->write($data, $auth);
        } catch (SocketStreamException $exception) {
            throw new RpcCallException($exception->getMessage(), $exception->getCode(), $exception);
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
     *
     * @throws \Iguan\Common\Data\JsonException
     * @throws RpcCallException if answer validating failed
     */
    private function readAndCheckAnswer($exceptedId)
    {
        $answer = $this->remoteClient->read();
        if (empty($answer)) throw new RpcCallException('Cannot read server response. Event server went away.');

        $decoder = new JsonDataDecoder();
        $answer = $decoder->decode($answer);

        //-----------------------------------------------------------v - yes, by design
        if ($answer === false || !isset($answer->id) || $answer->id != $exceptedId) {
            throw new RpcCallException('Bad server response. Error in JSON RPC format.');
        } else if (isset($answer->error)) {
            throw new RpcCallException('JSON RPC error: ' . $answer->error);
        }

        return isset($answer->data) ? $answer->data : [];
    }

    /**
     * When server answer are extracted and can be handled.
     *
     * @param string $method JSON PRC method name
     * @param mixed $answer server answer in 'data' JSON RPC reply field.
     */
    protected function onAnswerReceived($method, $answer)
    {

    }

    /**
     * Set a behavior when an RPC was executed.
     * If true, strategy will wait for server reply and
     * also perform answer validation. It can take a lot of time.
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
     * @param string $sourceTag current application/script tag
     *
     * @throws CommunicateException if action cannot be performed
     */
    public function register(Subject $subject, $sourceTag)
    {
        $way = $subject->getNotifyWay();
        $this->doSafeJsonRpcCall('Event.Register', [
                'sourceTag' => $sourceTag,
                'eventMask' => $subject->getEventName(),
                'subjects' => $way->getInfo()
            ]
        );
    }

    /**
     * Emit event descriptor to remote host using current socket
     * and encoder.
     * Encoder will be used to encode a PAYLOAD data, i.e. $descriptor.
     * Method will do a preparing data to send using JSON RPC.
     *
     * @param EventDescriptor $descriptor to be emitted.
     *
     * @throws CommunicateException in case of communicate error.
     */
    public final function emitEvent(EventDescriptor $descriptor)
    {
        $this->doSafeJsonRpcCall('Event.Fire', ['event' => $descriptor]);
    }

    /**
     * Cancel registration for passed subject.
     * This subject will never receive any invokes.
     *
     * @param Subject $subject to unsubscribe
     * @param string $sourceTag current application/script tag
     *
     * @throws CommunicateException if action cannot be performed
     */
    public function unRegister(Subject $subject, $sourceTag)
    {
        $way = $subject->getNotifyWay();
        $this->doSafeJsonRpcCall('Event.Unregister', [$sourceTag, $this->encoder->encode($way->getInfo())]);
    }

    /**
     * Cancel all registrations.
     *
     * @param string $sourceTag current application/script tag
     *
     * @throws CommunicateException if action cannot be performed
     */
    public function unRegisterAll($sourceTag)
    {
        $this->doSafeJsonRpcCall('Event.UnregisterAll', [$sourceTag]);
    }

    /**
     * Activate passed subject for being notified when
     * new event arrived.
     * Subject can be not registered, but, if event is
     * arrived, it subject will also be notified.
     * In case of remote communication, subject will be
     * notified right in subscribe call only once.
     * Before subscribe call on subscriber, all incoming
     * data must be initialized.
     *
     * @param Subject $subject to activate
     *
     * @throws \Iguan\Common\Data\EncodeDecodeException
     *                  if incoming events cannot be decoded using current decoder
     * @throws \Iguan\Event\Subscriber\Verificator\InvalidVerificationException
     *                  if payload cannot be trusted
     */
    public function subscribe(Subject $subject)
    {
        $way = $subject->getNotifyWay();
        $extractor = $this->getEventExtractor();
        $eventDescriptors = $extractor->extract($way);

        $this->notifyMatched($subject, $eventDescriptors);
    }

    public function setVerificator(Verificator $verificator)
    {
        $this->verificator = $verificator;
    }

    protected function getEventExtractor()
    {
        //can't store in field due to mutable verificator
        return new GlobalEventExtractor($this->decoder, $this->verificator);
    }
}
