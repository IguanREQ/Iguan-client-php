<?php

namespace Iguan\Event\Subscriber;

/**
 * Class UriPair.
 * Explode URI for two parts: fluent and app-based.
 * First one is useful for detecting same application launched on
 * multiples domains or sub-paths.
 * Second one is constant part of each URI which available
 * for current app.
 *
 * @author Vishnevskiy Kirill
 */

class UriPair
{
    /**
     * @var string domain-dependent part, i.e. app root
     */
    public $fluentPart;

    /**
     * @var string app-dependent part
     */
    public $appPart;

    /**
     * UriPair constructor.
     * @param string $fluentPart
     * @param string $appPart
     */
    public function __construct($fluentPart, $appPart)
    {
        $this->fluentPart = $fluentPart;
        $this->appPart = $appPart;
    }
}