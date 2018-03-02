<?php

namespace Iguan\Event\Builder;

use Iguan\Event\Subscriber\Guard\SubscriptionGuard;

/**
 * Class SubscriberGuardCreator
 * Creator for 'subscriber.guard' key.
 *
 * @author Vishnevskiy Kirill
 */
class SubscriberGuardCreator extends Creator
{
    const EXCEPTED_CONFIG_VALUES = [
        'class' => SubscriptionGuard::class
    ];

    /**
     * @return SubscriptionGuard
     */
    public function create()
    {
        $type = $this->getExceptedConfigValue('type');

        switch ($type) {
            case 'file' :
                return (self::getNextNode($this, SubscriberFileGuardCreator::class, 'file'))->create();
            default:
                $class = $this->getExceptedConfigValue('class');
                return new $class();
        }
    }
}