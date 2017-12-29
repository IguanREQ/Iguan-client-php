<?php

namespace Iguan\Event\Subscriber\Guard;

use Iguan\Event\Subscriber\Subject;

/**
 * Class SubscriptionGuard
 *
 * @author Vishnevskiy Kirill
 */
abstract class SubscriptionGuard
{
    private $appVersion;

    public function __construct($appVersion)
    {
        $this->appVersion = $appVersion;
    }

    /**
     * @return mixed
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    public abstract function hasSubscription(Subject $subject, $sourceTag);

    public abstract function persistSubscription(Subject $subject, $sourceTag);

    public abstract function isVersionChanged($sourceTag);
    public abstract function persistVersion($sourceTag);
}