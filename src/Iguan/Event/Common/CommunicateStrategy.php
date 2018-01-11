<?php

namespace Iguan\Event\Common;

use Iguan\Event\Dispatcher\RpcCallException;
use Iguan\Event\Subscriber\Subject;
use Iguan\Event\Subscriber\SubjectNotifier;
use PHPUnit\Runner\Exception;

/**
 * Class DispatchStrategy.
 * A base class for dispatching realizing.
 * If you wanna custom event handling in app,
 * just extends that class, realize DispatchStrategy::emitEvent
 * and set an strategy instance to EventDispatcher.
 *
 * @author Vishnevskiy Kirill
 */
abstract class CommunicateStrategy
{
    private $auth;
    private $notifier;

    /**
     * Emit event according to current strategy.
     *
     * @param EventDescriptor $descriptor event describer structure that must
     *                        be passed to recipient.
     *
     * @throws CommunicateException if action cannot be performed
     */
    public abstract function emitEvent(EventDescriptor $descriptor);

    /**
     * Register new subject as an event handler.
     * It does not mean, that subject is ready to receive an
     * events. For receiving events need to subscribe in EventSubscriber.
     *
     * @param Subject $subject to register
     * @param string $sourceTag current application/script tag
     *
     * @throws CommunicateException if action cannot be performed
     */
    public abstract function register(Subject $subject, $sourceTag);

    /**
     * Cancel registration for passed subject.
     * This subject will never receive any invokes.
     *
     * @param Subject $subject to unsubscribe
     * @param string $sourceTag current application/script tag
     *
     * @throws CommunicateException if action cannot be performed
     */
    public abstract function unRegister(Subject $subject, $sourceTag);

    /**
     * Cancel all registrations.
     *
     * @param string $sourceTag current application/script tag
     *
     * @throws CommunicateException if action cannot be performed
     */
    public abstract function unRegisterAll($sourceTag);

    /**
     * Activate passed subject for being notified when
     * new event arrived.
     * Subject can be not registered, but, if event is
     * arrived, it subject will also be notified.
     *
     * @param Subject $subject to activate
     * @throws \Iguan\Common\Data\EncodeDecodeException
     *                  if incoming events cannot be decoded using current decoder
     * @throws CommunicateException if action cannot be performed
     */
    public abstract function subscribe(Subject $subject);


    /**
     * Set auth that will be passed in fire event
     * request to recipient.
     * @param CommonAuth $auth for usage
     */
    public function setAuth(CommonAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return CommonAuth composed object based on current state
     */
    protected function getAuth()
    {
        if ($this->auth === null) $this->auth = new CommonAuth();

        return $this->auth;
    }

    /**
     * Notify subject, if any of incoming descriptors are matched with
     * subject rules.
     *
     * @param Subject $subject to be notified
     * @param EventDescriptor[] $descriptors an incoming events
     */
    protected function notifyMatched(Subject $subject, array $descriptors)
    {
        $this->getNotifier()->notifyMatched($subject, $descriptors);
    }

    protected function getNotifier()
    {
        if ($this->notifier === null) {
            $this->notifier = new SubjectNotifier();
        }

        return $this->notifier;
    }
}