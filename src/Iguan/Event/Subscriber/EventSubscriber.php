<?php

namespace Iguan\Event\Subscriber;


use Iguan\Event\Common\CommunicateStrategy;

class EventSubscriber
{

    /**
     * @var CommunicateStrategy
     */
    private $strategy;

    private $isNeedToRegisterOnSubscribe = true;

    public function __construct(CommunicateStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Indicate that subscriber must also register
     * subject in event manipulating point (event server or local
     * event queue)
     *
     * @param $needToRegister
     */
    public function registerOnSubscribe($needToRegister)
    {
        $this->isNeedToRegisterOnSubscribe = $needToRegister;
    }

    public function subscribe(Subject $subject)
    {
        if ($this->isNeedToRegisterOnSubscribe) {
            $this->register($subject);
        }
        $this->strategy->subscribe($subject);
    }

    public function register(Subject $subject)
    {
        $this->strategy->register($subject);
    }
}