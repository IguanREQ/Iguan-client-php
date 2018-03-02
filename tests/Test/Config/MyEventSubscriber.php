<?php
/**
 * Created by PhpStorm.
 * User: 119
 * Date: 01.03.2018
 * Time: 18:10
 */

namespace Test\Config;


use Iguan\Event\Common\CommunicateStrategy;
use Iguan\Event\Subscriber\EventSubscriber;

class MyEventSubscriber extends EventSubscriber
{
    private $sourceTag;
    /**
     * @var CommunicateStrategy
     */
    private $strategy;

    public function __construct($sourceTag, CommunicateStrategy $strategy)
    {
        parent::__construct($sourceTag, $strategy);
        $this->sourceTag = $sourceTag;
        $this->strategy = $strategy;
    }

    /**
     * @return CommunicateStrategy
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @return mixed
     */
    public function getSourceTag()
    {
        return $this->sourceTag;
    }

    public function unRegisterAll()
    {
        //prevent writing in tests
    }
}