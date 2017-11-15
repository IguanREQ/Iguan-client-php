<?php

namespace Iguan\Event\Dispatcher;

/**
 * Class RemoteSocketClient
 * @author Vishnevskiy Kirill
 */
class RemoteSocketClient
{
    const DEFAULT_REMOTE_PORT = 16986;

    private $remoteSocket;
    private $socketStream;
    private $overlapContext;
    private $socketError;
    private $socketErrorCode;
    private $socketFlags = STREAM_CLIENT_CONNECT;

    private $bufferSize = 1024;
    private $timeout = 30;
    private $contextArgs = [];

    public function __construct($remoteSocket = 'tcp://localhost:16986')
    {
        $this->remoteSocket = $remoteSocket;
    }

    public function setSocketContext($context)
    {
        $this->overlapContext = $context;
    }

    public function setCertificatePath($certLocalPath)
    {
        $this->contextArgs['ssl']['local_cert'] = $certLocalPath;
    }

    public function setWriteBufferSize($bufferSize)
    {
        $this->bufferSize = $bufferSize;
    }

    public function useAsync()
    {
        $this->socketFlags |= STREAM_CLIENT_ASYNC_CONNECT;
    }

    public function persist()
    {
        $this->socketFlags |= STREAM_CLIENT_PERSISTENT;
    }

    public function write($data)
    {

    }

    private function getSocketStream()
    {
        if ($this->socketStream !== null) return $this->socketStream;

        if ($this->overlapContext === null) {
            $context = stream_context_create($this->contextArgs);
        } else {
            $context = $this->overlapContext;
        }

        $socket = stream_socket_client($this->remoteSocket, $this->socketErrorCode, $this->socketError, $this->timeout, $this->socketFlags, $context);
    }
}