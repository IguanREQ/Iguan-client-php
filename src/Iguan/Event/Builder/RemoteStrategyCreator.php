<?php
namespace Iguan\Event\Builder;

use Iguan\Common\Data\DataCoderProvider;
use Iguan\Event\Common\Remote\RemoteCommunicateStrategy;

/**
 * Class RemoteStrategyCreator
 * Creator for 'common.remote' key.
 *
 * @author Vishnevskiy Kirill
 */
class RemoteStrategyCreator extends Creator
{

    const EXCEPTED_CONFIG_VALUES = [
        'class' => RemoteCommunicateStrategy::class,
        'payload_format' => [
            'values' => ['json', 'yaml']
        ],
        'wait_for_answer' => [
            'types' => ['boolean']
        ]
    ];

    /**
     * @return RemoteCommunicateStrategy
     */
    public function create()
    {
        $class = $this->getExceptedConfigValue('class', RemoteCommunicateStrategy::class);
        $payloadFormat = $this->getExceptedConfigValue('payload_format', 'json');
        $waitForAnswer = $this->getExceptedConfigValue('wait_for_answer', true);
        $strategyClient = self::getNextNode($this, RemoteStrategyClientCreator::class, 'client')->create();

        $dataEncoder = DataCoderProvider::getEncoderForFormat($payloadFormat);
        $dataDecoder = DataCoderProvider::getDecoderForFormat($payloadFormat);
        /** @var RemoteCommunicateStrategy $strategy */
        $strategy = new $class($strategyClient, $dataEncoder, $dataDecoder);
        $strategy->setWaitForAnswer($waitForAnswer);

        return $strategy;
    }

}