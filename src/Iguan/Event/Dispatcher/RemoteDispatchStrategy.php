<?php

namespace Iguan\Event\Dispatcher;
use Internal\Util\Encoder\DataEncoder;

/**
 * Class RemoteDispatchStrategy
 * @author Vishnevskiy Kirill
 */
class RemoteDispatchStrategy extends DispatchStrategy
{
    /**
     * @var RemoteSocketClient
     */
    private $socket;

    /**
     * RemoteDispatchStrategy constructor.
     * @param DataEncoder $encoder
     * @param RemoteSocketClient $socket
     */
    public function __construct(DataEncoder $encoder, RemoteSocketClient $socket)
    {
        parent::__construct($encoder);
        $this->socket = $socket;
    }


    public function emitEvent(EventDescriptor $describer)
    {
        $data = $this->encode($describer);
        $this->socket->write($data);
    }
}