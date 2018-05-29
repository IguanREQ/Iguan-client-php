<?php

namespace Iguan\Event\Subscriber;

use Iguan\Event\Common\EventDescriptor;

/**
 * Class Subject.
 * A subject is like a subscription.
 * Subject are described by token, some string,
 * that define what kind of events can be consumed
 * by subject. Also, subject define the way to be notified.
 * Subject can has a multiple handlers. Each of which
 * will be invoked when event arrived.
 *
 * @author Vishnevskiy Kirill
 */
class Subject
{
    /** @var string */
    private $eventName;

    /** @var \Closure[] */
    private $handlers = [];
    /**
     * @var SubjectNotifyWay
     */
    private $way;

    /**
     * Subject constructor.
     *
     * @param string $eventName event name @see Event::setName comment.
     * @param SubjectNotifyWay $way to be notified by
     */
    public function __construct($eventName, SubjectNotifyWay $way)
    {
        $this->eventName = $eventName;
        $this->way = $way;
    }

    /**
     * Add handler for this subject.
     * A handler will be invoked when new event
     * matched with subject are arrived.
     * Handler will receive a EventDescriptor instance of
     * incoming event in first argument.
     *
     * @param \Closure $closure with first argument in signature
     */
    public function addHandler(\Closure $closure)
    {
        $this->handlers[] = $closure;
    }

    /**
     * Notify all handlers with incoming event.
     *
     * @param EventDescriptor $descriptor
     */
    public function notifyAll(EventDescriptor $descriptor)
    {
        foreach ($this->handlers as $handler) {
            $handler($descriptor);
        }
    }

    /**
     * A subject token.
     *
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @return SubjectNotifyWay
     */
    public function getNotifyWay()
    {
        return $this->way;
    }
}