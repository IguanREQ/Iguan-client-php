<?php

namespace Iguan\Event\Builder;

use Iguan\Event\Common\CommunicateStrategy;

/**
 * Class StrategyCreator
 * Creator for 'common' key in config.
 * Actually, creator fo common strategy.
 *
 * @author Vishnevskiy Kirill
 */
class StrategyCreator extends Creator
{
    const EXCEPTED_CONFIG_VALUES = [
        'type' => ['remote']
    ];

    /**
     * @return CommunicateStrategy
     */
    public function create()
    {
        $strategyType = $this->getExceptedConfigValue('type', 'remote');

        switch ($strategyType) {
            case 'remote':
                $strategy = self::getNextNode($this, RemoteStrategyCreator::class, 'remote')->create();
                break;
            default:
                //unreachable
                $strategy = null;
        }

        /** @var CommunicateStrategy $strategy */
        if ($strategy !== null) {
            $auth = self::getNextNode($this, AuthCreator::class, 'auth')->create();
            $strategy->setAuth($auth);
        }

        return $strategy;
    }


}