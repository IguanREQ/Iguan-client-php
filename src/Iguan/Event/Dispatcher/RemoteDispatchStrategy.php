<?php

namespace Iguan\Event\Dispatcher;

use Iguan\Common\Encoder\DataEncoder;
use Iguan\Common\Encoder\JsonDataDecoder;
use Iguan\Common\Encoder\JsonDataEncoder;

/**
 * Class RemoteDispatchStrategy
 * A dispatch strategy, when event server is on remote
 * host and can be accessible via socket communication.
 *
 * @author Vishnevskiy Kirill
 */
class RemoteDispatchStrategy extends DispatchStrategy
{
    //if there is no auth on remote
    const AUTH_TYPE_NO_AUTH = 0;

    //auth by token (lower bit)
    const AUTH_TYPE_TOKEN = 1;

    //auth by token name (lower + 1 bit)
    const AUTH_TYPE_TOKEN_NAME = 2;

    /**
     * @var RemoteSocketClient
     */
    private $socket;

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
     * @param DataEncoder $encoder an encoder to encode payload data.
     *                    Ensure that server are support this encoding method.
     * @param RemoteSocketClient $socket an initialized remote socket
     *                           that will use to communicate with remote event server.
     */
    public function __construct(DataEncoder $encoder, RemoteSocketClient $socket)
    {
        $this->socket = $socket;
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
            'id' => $rpcId
        ];

        //can save a bit performance by using already right encoder
        if ($this->encoder instanceof JsonDataDecoder) {
            $jsonRpcData['params'] = [$descriptor];
            $data = $this->encoder->encode($jsonRpcData);
        } else {
            $encodedDescriptor = $this->encoder->encode($descriptor);
            $jsonRpcData['params'] = [$encodedDescriptor];
            $data = (new JsonDataEncoder())->encode($jsonRpcData);
        }
        try {
            $message = $this->composePayloadMessage($data);
            $this->socket->write($message);
        } catch (SocketStreamException $exception) {
            throw new EventDispatchException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($this->waitForAnswer) {
            $answer = $this->readAndCheckAnswer($rpcId);
            $this->onAnswerReceived($answer);
        }
    }

    /**
     * Create a raw payload message to be written in socket.
     * By default:
     * First byte - auth type byte (bit mask of self::AUTH_TYPE_* const)
     * Next, if has an AUTH_TYPE_TOKEN bit - first byte it's a token size in bytes, next - N bytes of token.
     * Next, if has an AUTH_TYPE_TOKEN_NAME bit - first byte it's a token name size in bytes, next - N bytes of token name.
     * Next - payload data.
     * LF byte at the end required!
     *
     * @param string $payloadData a prepared RPC call data.
     * @return string raw prepared binary string.
     */
    protected function composePayloadMessage($payloadData)
    {
        $type = self::AUTH_TYPE_NO_AUTH;

        $authToken = $this->getAuthToken();
        $authTokenLength = strlen($authToken);
        $isAuthTokenPresent = $authTokenLength !== 0;
        if ($isAuthTokenPresent) {
            $type |= self::AUTH_TYPE_TOKEN;
        }

        $authTokenName = $this->getAuthTokenName();
        $authTokenNameLength = strlen($authTokenName);
        $isAuthTokenNamePresent = $authTokenNameLength !== 0;
        if ($isAuthTokenNamePresent) {
            $type |= self::AUTH_TYPE_TOKEN_NAME;
        }

        $authType = pack('C', $type);
        $message = $authType;

        if ($isAuthTokenPresent) {
            $message .= pack('C', $authTokenLength) . $authToken;
        }

        if ($isAuthTokenNamePresent) {
            $message .= pack('C', $authTokenNameLength) . $authTokenName;
        }

        return $message . $payloadData . "\n";
    }

    /**
     * Read message for socket and validate it.
     *
     * @param string $exceptedId an RPC ID, that must be in server reply.
     * @return mixed server data reply.
     */
    private function readAndCheckAnswer($exceptedId)
    {
        $answer = $this->socket->readChunk();
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