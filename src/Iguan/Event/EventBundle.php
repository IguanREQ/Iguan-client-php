<?php

namespace Iguan\Event;

use Iguan\Common\ImmutableException;

/**
 * Class EventBundle
 * A bundle is a simple event data holder
 * that can be locked for modification
 * at any time.
 *
 * @author Vishnevskiy Kirill
 */
class EventBundle
{
    /**
     * @var string an event source class
     */
    private $class;

    /**
     * @var string event token. @see Event::setToken($token)
     */
    private $token;

    /**
     * @var mixed event payload data. @see Event::setPayload($payload)
     */
    private $payload;

    /**
     * @var bool a flag for indication of immutable state
     */
    private $immutableLock = false;

    /**
     * Mark current bundle as immutable.
     * Attempt to change bundle state will lead
     * to @see ImmutableException
     */
    public function lock() {
        $this->immutableLock = true;
    }

    /**
     * @return string a bundle stored class
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set event class to bundle.
     * If bundle is locked - it will raise
     * a @see ImmutableException
     *
     * @param string $class event class
     */
    public function setClass($class)
    {
        $this->checkImmutable();
        $this->class = $class;
    }

    /**
     * @return string a bundle stored event
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set event token to bundle.
     * If bundle is locked - it will raise
     * a @see ImmutableException
     *
     * @param string $token event token
     */
    public function setToken($token)
    {
        $this->checkImmutable();
        $this->token = $token;
    }

    /**
     * @return mixed a bundle stored payload
     */
    public function getPayload()
    {
        return $this->payload;
    }


    /**
     * Set event payload to bundle.
     * If bundle is locked - it will raise
     * a @see ImmutableException
     *
     * @param string $payload event payload
     */
    public function setPayload($payload)
    {
        $this->checkImmutable();
        $this->payload = $payload;
    }

    /**
     * @return array of private object fields
     */
    public function asArray() {
        return [
          'class' => $this->class,
          'token' => $this->token,
            'payload' => $this->payload
        ];
    }

    /**
     * @throws ImmutableException in case if bundle is locked
     */
    private function checkImmutable() {
        if ($this->immutableLock) throw new ImmutableException('Changing property of EventBundle directly is unacceptable.');
    }
}