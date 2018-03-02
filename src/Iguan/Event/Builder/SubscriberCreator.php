<?php
namespace Iguan\Event\Builder;

use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Subscriber\EventSubscriber;

/**
 * Class SubscriberCreator
 * Creator for 'subscriber' key.
 *
 * @author Vishnevskiy Kirill
 */
class SubscriberCreator extends Creator
{
    const EXCEPTED_CONFIG_VALUES = [
        'class' => EventSubscriber::class,
        'register_on_subscribe' => [
            'types' => ['boolean']
        ]
    ];
    /**
     * @var CommunicateStrategy
     */
    private $strategy;
    private $sourceTag;

    public function __construct(Config $config, $nodeRoot, CommunicateStrategy $strategy, $sourceTag) {
        parent::__construct($config, $nodeRoot);
        $this->strategy = $strategy;
        $this->sourceTag = $sourceTag;
    }

    /**
     * @return EventSubscriber
     * @throws \Iguan\Event\Common\CommunicateException
     */
    public function create()
    {
        $class = $this->getExceptedConfigValue('class', EventSubscriber::class);
        $registerOnSubscribe = $this->getExceptedConfigValue('register_on_subscribe', true);
        /** @var EventSubscriber $subscriber */
        $subscriber = new $class($this->sourceTag, $this->strategy);
        $subscriber->registerOnSubscribe($registerOnSubscribe);

        $guard = $this->getExceptedConfigValue('guard');
        if ($guard !== null) {
            $guard = self::getNextNode($this, SubscriberGuardCreator::class, 'guard')->create();
            $subscriber->setGuard($guard);
        }
        return $subscriber;
    }
}