<?php

namespace Iguan\Event\Common;

use Iguan\Event\Event;

/**
 * Class EventDescriptor
 *
 * @author Vishnevskiy Kirill
 */
class EventDescriptor
{
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

    /** @var Event */
    public $raisedEvent;
}