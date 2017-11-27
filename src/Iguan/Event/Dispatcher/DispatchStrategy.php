<?php

namespace Iguan\Event\Dispatcher;

use Iguan\Event\Common\CommonAuth;

/**
 * Class DispatchStrategy.
 * A base class for dispatching realizing.
 * If you wanna custom event handling in app,
 * just extends that class, realize DispatchStrategy::emitEvent
 * and set an strategy instance to EventDispatcher.
 *
 * @author Vishnevskiy Kirill
 */
abstract class DispatchStrategy
{
    private $auth;

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
}