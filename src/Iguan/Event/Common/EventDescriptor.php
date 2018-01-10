<?php

namespace Iguan\Event\Common;

use Iguan\Event\Event;

/**
 * Class EventDescriptor.
 * A wrapper over raw event instance with some support info.
 *
 * @author Vishnevskiy Kirill
 */
class EventDescriptor
{
    /** @var string application/script tag that raise event */
    public $sourceTag;

    /** @var array an event bundle packed into array */
    public $event;

    /** @var int a timestamp of event dispatching
     *  in microseconds since UNIX epoch
     */
    public $firedAt;

    /** @var int delay before event can be caught
     * by subscribers
     */
    public $delay;

    /** @var string a dispatcher language identifier */
    public $dispatcher;

    /** @var Event a deserialized incoming event */
    public $raisedEvent;
}