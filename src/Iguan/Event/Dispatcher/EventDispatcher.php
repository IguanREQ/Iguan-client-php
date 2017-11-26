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
 * Class EventDispatcher.
 * A main class for dispatching event using current
 * dispatch strategy which can a remote or local.
 *
 * @author Vishnevskiy Kirill
 */
class EventDispatcher
{
    /**
     * Dispatcher language identifier
     */
    const DISPATCHER_PHP = 1;

    /**
     * @var DispatchStrategy
     */
    private $strategy;

    /**
     * EventDispatcher constructor.
     *
     * @param DispatchStrategy $strategy define current dispatcher
     *                         a way to emit events.
     */
    public function __construct(DispatchStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * An alias for @see EventDispatcher::dispatchDelayed
     * with zero delaying.
     *
     * @param Event $event to be sent to listeners using
     *              current dispatch strategy.
     */
    public final function dispatch(Event $event)
    {
        $this->dispatchDelayed($event, 0);
    }

    /**
     * Do event emitting using current strategy.
     * Event will be packed to bundle and passed
     * to EventDispatcher::onEventReadyToEmit when descriptor will be
     * ready to sending.
     *
     * @param Event $event to be sent to listeners using
     *              current dispatch strategy.
     * @param int $delay_time_ms delay before event will be caught by
     *            listeners.
     */
    public final function dispatchDelayed(Event $event, $delay_time_ms)
    {
        $event_descriptor = new EventDescriptor();
        $event_descriptor->event = $event->pack()->asArray();
        $event_descriptor->firedAt = $this->getUnixMicrotime();
        $event_descriptor->delay = $delay_time_ms;
        $event_descriptor->dispatcher = self::DISPATCHER_PHP;

        $this->onEventReadyToEmit($event_descriptor);
    }

    /**
     * When event packed and prepared for sending to listeners.
     *
     * @param EventDescriptor $event_descriptor event meta data
     */
    protected function onEventReadyToEmit(EventDescriptor $event_descriptor)
    {
        $this->strategy->emitEvent($event_descriptor);
    }

    private function getUnixMicrotime()
    {
        return microtime(true) * 1000000;
    }
}