<?php

namespace Iguan\Event\Subscriber;


use Iguan\Event\Common\EventDescriptor;
use Iguan\Event\Event;

abstract class Subject
{
    private $token;

    /** @var \Closure[] */
    private $handlers = [];
    /**
     * @var SubjectNotifyWay
     */
    private $way;

    public function __construct($token, SubjectNotifyWay $way)
    {
        $this->token = $token;
        $this->way = $way;
    }

    public function addHandler(\Closure $closure)
    {
        $this->handlers[] = $closure;
    }

    public function invoke(EventDescriptor $descriptor)
    {
        $this->notify($descriptor->raisedEvent);
    }

    public function notify(Event $event)
    {
        foreach ($this->handlers as $handler) {
            $handler($event);
        }
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return SubjectNotifyWay
     */
    public function getNotifyWay()
    {
        return $this->way;
    }
}