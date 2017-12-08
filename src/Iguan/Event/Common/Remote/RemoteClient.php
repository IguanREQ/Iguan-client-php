<?php

namespace Iguan\Event\Common\Remote;

use Iguan\Event\Common\CommonAuth;

/**
 * Class RemoteClient
 * A client for remote communication.
 * A way to communicate must be refined in derived classes.
 *
 * @author Vishnevskiy Kirill
 */
abstract class RemoteClient
{
    /**
     * @param string $payload a data to be written as body
     * @param CommonAuth $commonAuth an extra data for authorization on remote
     */
    public abstract function write($payload, CommonAuth $commonAuth = null);

    /**
     * Read data from remote using current communication type.
     *
     * @return string server response
     */
    public abstract function read();
}