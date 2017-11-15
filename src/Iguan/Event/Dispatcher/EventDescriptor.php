<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 12.11.2017
 * Time: 15:00
 */

namespace Iguan\Event\Dispatcher;

/**
 * Class EventDescriptor
 * @author Vishnevskiy Kirill
 */
class EventDescriptor
{
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
}