<?php

namespace Iguan\Event\Dispatcher;

/**
 * Class RemoteSocketClient
 * A simple wrapper on PHP socket functions.
 *
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

    private $timeout = 30;
    private $contextArgs = [];

    /**
     * RemoteSocketClient constructor.
     *
     * @param string $remoteSocket a remote socket URI.
     */
    public function __construct($remoteSocket = null)
    {
        if ($remoteSocket === null) {
            $remoteSocket = 'tcp://127.0.0.1:' . self::DEFAULT_REMOTE_PORT;
        }
        $this->remoteSocket = $remoteSocket;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if ($this->socketStream !== null && is_resource($this->socketStream)) {
            fclose($this->socketStream);
        }
    }

    /**
     * Reset stored connection.
     * Next read/write operation will invoke new socket creation.
     */
    public function reset()
    {
        $this->socketStream = null;
    }

    /**
     * Override default class context with passed one.
     *
     * @param resource $context initialized socket context via stream_context_create(...)
     */
    public function setSocketContext($context)
    {
        $this->overlapContext = $context;
    }

    /**
     * Set ssl certificate location for enabling ssl/tls.
     *
     * @param string $certLocalPath
     */
    public function setCertificatePath($certLocalPath)
    {
        $this->contextArgs['ssl']['local_cert'] = $certLocalPath;
    }

    /**
     * Keep socket between processes.
     */
    public function persist()
    {
        $this->socketFlags |= STREAM_CLIENT_PERSISTENT;
    }

    /**
     * Write data to socket using current context.
     *
     * @param string $data to write
     * @throws SocketStreamException in case of error in socket writing
     */
    public function write($data)
    {
        $socket = $this->getSocketStream();
        $zeroWriteTries = 0;
        $zeroWriteMaxTries = 10;
        for ($written = 0; $written < strlen($data); $written += $writeCount) {
            $writeCount = fwrite($socket, substr($data, $written));
            if ($writeCount === 0 && $zeroWriteTries++ > $zeroWriteMaxTries) {
                throw new SocketStreamException('Unable to write event to socket stream.');
            }

            if ($writeCount === false) {
                throw new SocketStreamException('Unable to write event to socket stream.');
            }
        }
    }

    /**
     * Guaranty read required bytes from socket stream.
     * Method not released until all bytes will be read.
     *
     * @param int $exceptedLength bytes to read from socked
     * @return string socket read data
     */
    public function read($exceptedLength = 8196)
    {
        $data = '';
        for ($toRead = $exceptedLength; $toRead > 0; $toRead = $exceptedLength - strlen($data)) {
            $data .= $this->readChunk($exceptedLength);
        }

        return $data;
    }

    /**
     * Read a chunk from socket in block mode.
     *
     * @param int $chunkLength bytes to read
     * @return bool|string data, that may differ by length with excepted
     */
    public function readChunk($chunkLength = 8196)
    {
        return fread($this->getSocketStream(), $chunkLength);
    }

    /**
     * Lazy socket initializer function.
     *
     * @return resource a valid initialized using current state socket resource.
     */
    private function getSocketStream()
    {
        if ($this->socketStream !== null && is_resource($this->socketStream)) return $this->socketStream;

        if ($this->overlapContext === null) {
            $context = stream_context_create($this->contextArgs);
        } else {
            $context = $this->overlapContext;
        }

        $socket = stream_socket_client($this->remoteSocket, $this->socketErrorCode, $this->socketError, $this->timeout, $this->socketFlags, $context);
        if ($socket === false) {
            throw new SocketStreamException('Unable to create event socket stream: ' . $this->socketError . ' (' . $this->socketErrorCode . ').');
        }

        $this->socketStream = $socket;

        return $socket;
    }
}