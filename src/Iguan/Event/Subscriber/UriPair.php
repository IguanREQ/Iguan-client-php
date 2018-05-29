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
 * Also, pair define a way in which server take a decision
 * of choosing candidates for invoking.
 * If server have N same subscriptions (same tag, same event name),
 * there is a some problem in choosing: which one
 * must be invoke? One or all?
 * To help server, pair can bring some addition information
 * by specifying invoke kind.
 * If pair says that need to invoke only one, server will
 * choose ONE subscription base on next criteria:
 * app tag, subscription event name matched incoming one
 * and an app part of URI pair.
 * In other words, if you have TWO same applications
 * started on TWO different domains: 'example.com/some' and 'some.com/example',
 * and event handler is on 'index.php' and you want to guarantee that only
 * one handler receive same event, you have to separate URI like next one:
 * fluentPart: 'example.com/some/' or 'some.com/example/' depend on where is subscription made
 * appPart: 'index.php'
 * invokeKind: UriPair::INVOKE_KIND_ONCE
 *
 * If you want to consume events on each, just set invokeKind to UriPair::INVOKE_KIND_EACH.
 *
 * @author Vishnevskiy Kirill
 */

class UriPair
{
    const INVOKE_KIND_ONCE = 1;
    const INVOKE_KIND_EACH = 2;

    /**
     * @var string domain-dependent part, i.e. app root
     */
    public $fluentPart;

    /**
     * @var string app-dependent part
     */
    public $appPart;

    /**
     * @var int
     */
    public $invokeKind;

    /**
     * UriPair constructor.
     * @param string $fluentPart
     * @param string $appPart
     * @param int $invokeKind
     */
    public function __construct($fluentPart, $appPart, $invokeKind = self::INVOKE_KIND_ONCE)
    {
        $this->fluentPart = $fluentPart;
        $this->appPart = $appPart;
        $this->invokeKind = $invokeKind;
    }
}