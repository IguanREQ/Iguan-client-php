<?php

namespace Iguan\Event;

use Iguan\Common\ImmutableException;
use Iguan\Common\Util\Variable;

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
     * @var string event name. @see Event::setName($name)
     */
    private $name;

    /**
     * @var mixed event payload data. @see Event::setPayload($payload)
     */
    private $payload;

    /** @var string @see Event::setPayloadType($payloadType)*/
    private $payloadType;

    /**
     * @var bool a flag for indication of immutable state
     */
    private $immutableLock = false;

    /**
     * Mark current bundle as immutable.
     * Attempt to change bundle state will lead
     * to @see ImmutableException
     */
    public function lock()
    {
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set event name to bundle.
     * If bundle is locked - it will raise
     * a @see ImmutableException
     *
     * @param string $name event name
     */
    public function setName($name)
    {
        $this->checkImmutable();
        $this->name = $name;
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

        if ($this->getPayloadType() !== Variable::getTrueType($payload)) {
            $this->payload = Variable::cast($payload, $this->getPayloadType());
        }
    }

    /**
     * Set type of stored payload for recovering
     * right type on other side.
     *
     * @param string $payloadType
     */
    public function setPayloadType($payloadType)
    {
        $this->checkImmutable();
        $this->payloadType = $payloadType;
    }

    /**
     * @return mixed
     */
    public function getPayloadType()
    {
        if ($this->payloadType !== null) return $this->payloadType;
        $this->payloadType = Variable::getTrueType($this->getPayload());
        return $this->payloadType;
    }

    /**
     * @return array of private object fields
     */
    public function asArray()
    {
        return [
            'class' => $this->getClass(),
            'name' => $this->getName(),
            'payload' => $this->getPayload(),
            'payloadType' => $this->getPayloadType()
        ];
    }

    /**
     * @throws ImmutableException in case if bundle is locked
     */
    private function checkImmutable()
    {
        if ($this->immutableLock) throw new ImmutableException('Changing property of EventBundle directly is unacceptable.');
    }
}