<?php

namespace Iguan\Event\Common;

use Iguan\Event\Dispatcher\EventDispatchException;
use Iguan\Event\Subscriber\Subject;
use Iguan\Event\Subscriber\SubjectNotifier;

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
     * @throws EventDispatchException in case of any dispatch error
     */
    public abstract function emitEvent(EventDescriptor $descriptor);

    /**
     * Register new subject as an event handler.
     * It does not mean, that subject is ready to receive an
     * events. For receiving events need to subscribe in EventSubscriber.
     *
     * @param Subject $subject to register
     */
    public abstract function register(Subject $subject, $sourceTag);

    /**
     * Cancel registration for passed subject.
     * This subject will never receive any invokes.
     *
     * @param Subject $subject to unsubscribe
     * @param $sourceTag
     * @return
     */
    public abstract function unRegister(Subject $subject, $sourceTag);

    public abstract function unRegisterAll($sourceTag);

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
        return $this->auth;
    }

    protected function notifyMatched(Subject $subject, array $descriptors)
    {
        $this->getNotifier()->notifyMatched($subject, $descriptors);
    }

    private function getNotifier()
    {
        if ($this->notifier === null) {
            $this->notifier = new SubjectNotifier();
        }

        return $this->notifier;
    }
}