<?php

namespace Iguan\Event;
use Iguan\Event\Common\EventDescriptor;

/**
 * Class Event
 * Is a base class for event messaging.
 * You free to implement own event classes,
 * but don't forget to implement this one.
 *
 * @author Vishnevskiy Kirill
 */
class Event
{
    /**
     * @var EventBundle
     *
     * A bundle that contain all event data.
     * Bundle owned by Event and cannot be
     * modified directly after getting one.
     */
    private $bundle;

    /**
     * @var bool
     *
     * A flag that indicate stopped propagation
     * on current event.
     */
    private $prevented;


    /** @var EventDescriptor */
    private $descriptor;


    /**
     * Event constructor.
     *
     * @param EventBundle|null $bundle if specified, event
     * will be initialized with bundle data (@see Event::unpack()).
     */
    public function __construct(EventBundle $bundle = null)
    {
        $this->bundle = new EventBundle();
        $this->bundle->setClass(static::class);

        if ($bundle !== null) {
            $this->unpack($bundle);
        }
    }

    /**
     * Pack event data into ready-to-send package.
     *
     * @return EventBundle immutable bundle
     */
    public final function pack()
    {
        $this->bundle->lock();

        return $this->bundle;
    }

    /**
     * Initialize event with bundle data.
     *
     * @param EventBundle $bundle will be copied
     * to own event bundle.
     */
    public function unpack(EventBundle $bundle)
    {
        $this->bundle->setPayloadType($bundle->getPayloadType());
        $this->setPayload($bundle->getPayload());
        $this->setName($bundle->getName());
    }

    /**
     * Set name to current event.
     * @param string $key
     * A name - is a string, that describe event.
     * Name can be separated on domains by dot (".")
     * Listeners will be notified in case of:
     * 1. Full matching with expected name and event name.
     * 2. Partial matching with wildcards (*).
     *    Wildcard are can be any of one name domain.
     *    For example, a name like 'entity.*' means,
     *    that all subscriber for 'entity.<any_word>' will receive
     *    event.
     * 3. Partial matching with sharp (#).
     *    Unlike wildcard, sharp will replace all
     *    remains name domain on right side.
     *    For example, a name like 'entity.#' means,
     *    that all subscriber for 'entity.attribute.action'
     *    or 'entity.attribute' will receive event.
     */
    public function setName($key)
    {
        $this->bundle->setName($key);
    }

    /**
     * @return string a current event name
     */
    public function getName()
    {
        return $this->bundle->getName();
    }

    /**
     * Set event payload data.
     *
     * @param mixed $payload
     * A payload must be serializable by current
     * EventDispatcher strategy.
     * Usually, arrays or objects with public fields.
     */
    public function setPayload($payload)
    {
        $this->bundle->setPayload($payload);
    }

    /**
     * @return mixed a current event payload data
     */
    public function getPayload()
    {
        return $this->bundle->getPayload();
    }

    /**
     * Stop event propagation for next event
     * listeners that applied for current name event.
     */
    public function stopPropagation() {
        $this->prevented = true;
    }

    /**
     * @return bool true if event propagation stopped
     */
    public function isPrevented() {
        return $this->prevented;
    }

    /**
     * Create simple event object from passe data.
     *
     * @param string $name event name
     * @param mixed $payload event payload data
     * @return Event initialized event object that ready to send
     */
    public static function create($name, $payload)
    {
        $bundle = new EventBundle();
        $bundle->setName($name);
        $bundle->setPayload($payload);
        return new static($bundle);
    }

    /**
     * Get a descriptor from which current event
     * was created.
     *
     * @return EventDescriptor
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * Set a descriptor from which current event
     * was created.
     *
     * @param EventDescriptor $descriptor
     */
    public function setDescriptor($descriptor)
    {
        $this->descriptor = $descriptor;
    }
}