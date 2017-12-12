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
    private $sourceTag;

    public function __construct($sourceTag, CommunicateStrategy $strategy)
    {
        $this->strategy = $strategy;
        $this->sourceTag = $sourceTag;
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
        $this->strategy->register($subject, $this->sourceTag);
    }

    public function unRegister(Subject $subject)
    {
        $this->strategy->unRegister($subject, $this->sourceTag);
    }

    public function unRegisterAll()
    {
        $this->strategy->unRegisterAll($this->sourceTag);
    }
}