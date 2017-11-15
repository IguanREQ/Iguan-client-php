<?php

namespace Iguan\Event;

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
        $this->setPayload($bundle->getPayload());
        $this->setToken($bundle->getToken());
    }

    /**
     * Set source application or domain name identifier.
     *
     * @param string $source_id
     */
    public function setSourceId($source_id) {
        $this->bundle->setSourceId($source_id);
    }

    /**
     * @return number|string event source identifier
     */
    public function getSourceId() {
        return $this->bundle->getSourceId();
    }

    /**
     * Set token to current event.
     * @param string $key
     * A token - is a string, that describe event.
     * Token can be separated on domains by dot (".")
     * Listeners will be notified in case of:
     * 1. Full matching with excepted token and event token.
     * 2. Partial matching with wildcards (*).
     *    Wildcard are can be any of one token domain.
     *    For example, a token like 'entity.*' means,
     *    that all subscriber for 'entity.<any_word>' will receive
     *    event.
     * 3. Partial matching with sharp (#).
     *    Unlike wildcard, sharp will replace all
     *    remains token domain.
     *    For example, a token like 'entity.#' means,
     *    that all subscriber for 'entity.attribute.action'
     *    or 'entity.attribute' will receive event.
     */
    public function setToken($key)
    {
        $this->bundle->setToken($key);
    }

    /**
     * @return string a current event token
     */
    public function getToken()
    {
        return $this->bundle->getToken();
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
     * listeners that applied for current token event.
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
     * @param string $token event token
     * @param mixed $payload event payload data
     * @param string $sourceId event source identifier
     * @return Event initialized event object that ready to send
     */
    public static function create($token, $payload, $sourceId = '') {
        $bundle = new EventBundle();
        $bundle->setToken($token);
        $bundle->setPayload($payload);
        $bundle->setSourceId($sourceId);
        return new static($bundle);
    }
}