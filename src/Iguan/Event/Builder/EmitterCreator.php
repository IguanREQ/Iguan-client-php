<?php
namespace Iguan\Event\Builder;

use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Emitter\EventEmitter;

/**
 * Class EmitterCreator
 * Creator for 'emitter' key of config.
 *
 * @author Vishnevskiy Kirill
 */
class EmitterCreator extends Creator
{
    private $strategy;
    private $sourceTag;

    public function __construct(Config $config, $nodeRoot, CommunicateStrategy $strategy, $sourceTag) {
        parent::__construct($config, $nodeRoot);
        $this->strategy = $strategy;
        $this->sourceTag = $sourceTag;
    }

    /**
     * @return EventEmitter
     */
    public function create()
    {
        $class = $this->getExceptedConfigValue('class', EventEmitter::class);
        return new $class($this->sourceTag, $this->strategy);
    }
}