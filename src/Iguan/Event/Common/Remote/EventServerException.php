<?php

namespace Iguan\Event\Common\Remote;

use Iguan\Event\Dispatcher\EventDispatchException;

/**
 * Class EventServerException
 * Top-level dispatching exceptions, when
 * is not possible to do remote communication.
 *
 * @author Vishnevskiy Kirill
 */
class EventServerException extends EventDispatchException
{

}