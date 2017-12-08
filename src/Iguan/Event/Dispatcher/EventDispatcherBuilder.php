<?php

namespace Iguan\Event\Dispatcher;

use Iguan\Event\Common\Remote\RemoteCommunicateStrategy;
use Iguan\Event\Common\Remote\RemoteSocketClient;

/**
 * Class EventDispatcherBuilder
 *
 * @author Vishnevskiy Kirill
 */
class EventDispatcherBuilder
{
    private $strategyClass;
    private $strategyArgs = [];
    private $dispatcherClass;

    public function __construct()
    {
        $this->strategyClass = RemoteCommunicateStrategy::class;
        $this->dispatcherClass = EventDispatcher::class;
    }

    public function setRemoteSocket(RemoteSocketClient $remoteSocket) {
        $this->strategyClass = RemoteCommunicateStrategy::class;
        $this->strategyArgs = [$remoteSocket];

        return $this;
    }

    public function build() {
        $strategy = new ($this->strategyClass)(... $this->strategyArgs);
        $dispatcher = new ($this->dispatcherClass)($strategy);

        return $dispatcher;
    }
}