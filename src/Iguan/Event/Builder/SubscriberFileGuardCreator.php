<?php
namespace Iguan\Event\Builder;

use Iguan\Event\Subscriber\Guard\SubscriptionFileGuard;


/**
 * Class SubscriberFileGuardCreator
 * Creator for 'subscriber.guard.file' key.
 *
 * @author Vishnevskiy Kirill
 */
class SubscriberFileGuardCreator extends Creator
{
    const EXCEPTED_CONFIG_VALUES = [
        'lock_files_location' => [
            'types' => ['string']
        ],
        'app_version' => [
            'types' => ['string']
        ]
    ];

    /**
     * @return SubscriptionFileGuard
     */
    public function create()
    {
        $tmpPath = $this->getExceptedConfigValue('lock_files_location', '/tmp');
        $appVersion = $this->getExceptedConfigValue('app_version', '1.0');
        return new SubscriptionFileGuard($appVersion, $tmpPath);
    }
}