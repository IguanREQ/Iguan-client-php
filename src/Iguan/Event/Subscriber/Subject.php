<?php

namespace Iguan\Event\Subscriber;


use Iguan\Event\Common\EventDescriptor;

class Subject
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

    public function notifyAll(EventDescriptor $descriptor)
    {
        foreach ($this->handlers as $handler) {
            $handler($descriptor);
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