<?php

namespace Iguan\Event\Subscriber;


use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Subscriber\Guard\SubscriptionGuard;

class EventSubscriber
{

    /**
     * @var CommunicateStrategy
     */
    private $strategy;

    /**
     * @var SubscriptionGuard
     */
    private $guard;


    private $isNeedToRegisterOnSubscribe = true;
    private $sourceTag;

    public function __construct($sourceTag, CommunicateStrategy $strategy)
    {
        $this->strategy = $strategy;
        $this->sourceTag = $sourceTag;
    }

    public function setGuard(SubscriptionGuard $guard)
    {
        $this->guard = $guard;

        if ($guard !== null && $guard->isVersionChanged($this->sourceTag)) {
            $this->unRegisterAll();
            $guard->persistVersion($this->sourceTag);
        }
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
        if ($this->guard !== null && $this->guard->hasSubscription($subject, $this->sourceTag)) return;

        $this->strategy->register($subject, $this->sourceTag);

        if ($this->guard !== null) {
            $this->guard->persistSubscription($subject, $this->sourceTag);
        }
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