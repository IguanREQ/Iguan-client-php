<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 12.11.2017
 * Time: 14:44
 */

namespace Iguan\Event\Dispatcher;

/**
 * Class EventDispatcherBuilder
 * @author Vishnevskiy Kirill
 */
class EventDispatcherBuilder
{
    private $strategyClass;
    private $strategyArgs = [];
    private $dispatcherClass;

    public function __construct()
    {
        $this->strategyClass = RemoteDispatchStrategy::class;
        $this->dispatcherClass = EventDispatcher::class;
    }

    public function setRemoteSocket(RemoteSocketClient $remoteSocket) {
        $this->strategyClass = RemoteDispatchStrategy::class;
        $this->strategyArgs = [$remoteSocket];

        return $this;
    }

    public function build() {
        $strategy = new ($this->strategyClass)(... $this->strategyArgs);
        $dispatcher = new ($this->dispatcherClass)($strategy);

        return $dispatcher;
    }
}