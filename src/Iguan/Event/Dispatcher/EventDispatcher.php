<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 12.11.2017
 * Time: 14:40
 */

namespace Iguan\Event\Dispatcher;

use Iguan\Event\Event;

/**
 * Class EventDispatcher
 * @author Vishnevskiy Kirill
 */
class EventDispatcher
{
    const DISPATCHER_PHP = 1;

    /**
     * @var DispatchStrategy
     */
    private $strategy;

    public function __construct(DispatchStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public final function dispatch(Event $event)
    {
        $this->dispatchDelayed($event, 0);
    }

    public final function dispatchDelayed(Event $event, $delay_time_ms)
    {
        $event_descriptor = new EventDescriptor();
        $event_descriptor->event = $event->pack()->asArray();
        $event_descriptor->firedAt = $this->getUnixMicrotime();
        $event_descriptor->delay = $delay_time_ms;
        $event_descriptor->dispatcher = self::DISPATCHER_PHP;

        $this->onEventReadyToEmit($event_descriptor);
    }

    protected function onEventReadyToEmit(EventDescriptor $event_descriptor)
    {
        $this->strategy->emitEvent($event_descriptor);
    }

    private function getUnixMicrotime()
    {
        return microtime(true) * 1000000;
    }
}