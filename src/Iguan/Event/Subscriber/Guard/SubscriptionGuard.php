<?php

namespace Iguan\Event\Subscriber\Guard;

use Iguan\Event\Subscriber\Subject;

/**
 * Class SubscriptionGuard
 * A guard can be used for reduce remote event server
 * calls and optimize performance. Based on app version, source tag and
 * subject unique combination.
 *
 * @author Vishnevskiy Kirill
 */
abstract class SubscriptionGuard
{
    /** @var string */
    private $appVersion;

    /**
     * SubscriptionGuard constructor.
     * @param string $appVersion current app version or tag
     */
    public function __construct($appVersion)
    {
        $this->appVersion = $appVersion;
    }

    /**
     * @return string current app version or tag
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * Check if guard already has cached subscription for version, passed subject and source tag.
     *
     * @param Subject $subject to be checked
     * @param string $sourceTag application/script tag
     * @return bool true, if subscription are still valid
     */
    public abstract function hasSubscription(Subject $subject, $sourceTag);

    /**
     * Cache subscription.
     * A current app version, subject and source tag must combine exclusive
     * stamp.
     *
     * @param Subject $subject to cache
     * @param string $sourceTag application/script tag
     */
    public abstract function persistSubscription(Subject $subject, $sourceTag);

    /**
     * Check if version of app changed since last execution.
     *
     * @param string $sourceTag application/script tag
     * @return bool true if version updated
     */
    public abstract function isVersionChanged($sourceTag);

    /**
     * Cache version.
     * A version MUST be cached in pair with source tag.
     *
     * @param string $sourceTag application/script tag
     */
    public abstract function persistVersion($sourceTag);
}