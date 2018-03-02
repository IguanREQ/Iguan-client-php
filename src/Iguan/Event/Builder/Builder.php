<?php

namespace Iguan\Event\Builder;

/**
 * Class Builder
 * A builder for composing main objects
 * for Iguan using config.
 *
 * It's a preferred way for Iguan object creation,
 * but you feel free to construct any objects by yourself.
 *
 * @author Vishnevskiy Kirill
 */
class Builder
{
    /**
     * @var Config
     */
    private $config;

    private $strategy;
    private $subscriber;
    private $emitter;

    /**
     * Builder constructor.
     * @param Config $config from which all object will be created.
     *        Mutating config after creating some objects - bad idea.
     *        If you don't think so, call Builder::clear() on
     *        config changes to reset optimisations.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Build instance of subscriber according to config.
     *
     * @return \Iguan\Event\Subscriber\EventSubscriber
     * @throws \Iguan\Event\Common\CommunicateException if instance cannot to use guard normal
     */
    public function buildSubscriber() {
        $tag = $this->config->getValue('common.tag' ,'NO TAG');
        $strategy = $this->createStrategy();
        return $this->createSubscriber($tag, $strategy);
    }

    /**
     * Build instance of emitter according to config.
     *
     * @return \Iguan\Event\Emitter\EventEmitter
     */
    public function buildEmitter() {
        $tag = $this->config->getValue('common.tag' ,'NO TAG');
        $strategy = $this->createStrategy();
        return $this->createEmitter($tag, $strategy);
    }

    private function createStrategy() {
        if ($this->strategy !== null) return $this->strategy;

        $this->strategy = (new StrategyCreator($this->config, 'common'))->create();

        return $this->strategy;
    }

    /**
     * @param $tag
     * @param $strategy
     * @return \Iguan\Event\Subscriber\EventSubscriber
     * @throws \Iguan\Event\Common\CommunicateException
     */
    private function createSubscriber($tag, $strategy) {
        if ($this->subscriber !== null) return $this->subscriber;

        $this->subscriber = (new SubscriberCreator($this->config, 'subscriber', $strategy, $tag))->create();

        return $this->subscriber;
    }

    private function createEmitter($tag, $strategy) {
        if ($this->emitter !== null) return $this->emitter;

        $this->emitter = (new EmitterCreator($this->config, 'emitter', $strategy, $tag))->create();

        return $this->emitter;
    }

    /**
     * Reset lazy-creation mode.
     */
    public function reset() {
        $this->strategy = null;
        $this->subscriber = null;
        $this->emitter = null;
    }
}