<?php

namespace Iguan\Event\Subscriber;

use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Subscriber\Guard\SubscriptionGuard;

/**
 * Class EventSubscriber
 * A main class for operating over Subjects.
 *
 * @author Vishnevskiy Kirill
 */
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

    /**
     * @var bool
     */
    private $isNeedToRegisterOnSubscribe = true;

    /**
     * @var string
     */
    private $sourceTag;

    /**
     * EventSubscriber constructor.
     *
     * @param string $sourceTag application/script tag
     * @param CommunicateStrategy $strategy define current strategy for subscribing,
     *                            registering and invoking.
     */
    public function __construct($sourceTag, CommunicateStrategy $strategy)
    {
        $this->strategy = $strategy;
        $this->sourceTag = $sourceTag;
    }

    /**
     * Set a guard for subscriber.
     * Guard will be used in each register call
     * for reducing calling strategy proxy.
     *
     * @param SubscriptionGuard $guard
     * @throws \Iguan\Event\Common\CommunicateException
     */
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

    /**
     * Activate subject for receiving events.
     * If $this->isNeedToRegisterOnSubscribe flag are set
     * method also do $this->register call for registration on
     * strategy proxy.
     * Subject can be not registered, but, if event is
     * arrived, subject will also be notified.
     *
     * @param Subject $subject to be activated
     * @throws \Iguan\Common\Data\EncodeDecodeException
     * @throws \Iguan\Event\Common\CommunicateException
     * @throws \Iguan\Event\Subscriber\Verificator\InvalidVerificationException
     */
    public function subscribe(Subject $subject)
    {
        if ($this->isNeedToRegisterOnSubscribe) {
            $this->register($subject);
        }
        $this->strategy->subscribe($subject);
    }

    /**
     * Register subject on strategy proxy.
     * If guard present, firstly it was asked for cached
     * subscription for subject.
     * If there is no cached data, method do a
     * proxy register call and tries to persist
     * subscription if guard present.
     *
     * @param Subject $subject to be registered
     * @throws \Iguan\Event\Common\CommunicateException
     */
    public function register(Subject $subject)
    {
        if ($this->guard !== null && $this->guard->hasSubscription($subject, $this->sourceTag)) return;

        $this->strategy->register($subject, $this->sourceTag);

        if ($this->guard !== null) {
            $this->guard->persistSubscription($subject, $this->sourceTag);
        }
    }

    /**
     * Cancel registration of subject on proxy.
     *
     * @param Subject $subject to cancel
     * @throws \Iguan\Event\Common\CommunicateException
     */
    public function unRegister(Subject $subject)
    {
        $this->strategy->unRegister($subject, $this->sourceTag);
    }

    /**
     * Cancel all registrations on proxy.
     *
     * @throws \Iguan\Event\Common\CommunicateException
     */
    public function unRegisterAll()
    {
        $this->strategy->unRegisterAll($this->sourceTag);
    }
}