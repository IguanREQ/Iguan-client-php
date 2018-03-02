<?php

namespace Iguan\Event\Emitter;

use Iguan\Event\Common\CommunicateException;
use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Event;

/**
 * Class EventDispatcher.
 * A main class for dispatching event using current
 * dispatch strategy which can a remote or local.
 *
 * @author Vishnevskiy Kirill
 */
class EventEmitter
{
    /**
     * Dispatcher language identifier
     */
    const DISPATCHER_PHP = 1;

    /**
     * @var CommunicateStrategy
     */
    private $strategy;

    /**
     * @var string
     */
    private $sourceTag;

    /**
     * EventDispatcher constructor.
     *
     * @param string $sourceTag application/script tag
     * @param CommunicateStrategy $strategy define current dispatcher
     *                         a way to emit events.
     */
    public function __construct($sourceTag, CommunicateStrategy $strategy)
    {
        $this->strategy = $strategy;
        $this->sourceTag = $sourceTag;
    }

    /**
     * An alias for @see EventEmitter::dispatchDelayed
     * with zero delaying.
     *
     * @param Event $event to be sent to listeners using
     *              current dispatch strategy.
     *
     * @throws CommunicateException in case of communicate error.
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
     *
     * @throws CommunicateException in case of communicate error.
     */
    public final function dispatchDelayed(Event $event, $delay_time_ms)
    {
        $event_descriptor = new EventDescriptor();
        $event_descriptor->sourceTag = $this->sourceTag;
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
     *
     * @throws CommunicateException in case of communicate error.
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