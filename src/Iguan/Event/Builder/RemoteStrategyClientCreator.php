<?php
namespace Iguan\Event\Builder;

use Iguan\Event\Common\Remote\RemoteSocketClient;

/**
 * Class RemoteStrategyClientCreator
 * Creator for 'common.remote.client' key.
 *
 * @author Vishnevskiy Kirill
 */
class RemoteStrategyClientCreator extends Creator
{

    const EXCEPTED_CONFIG_VALUES = [
        'class' => RemoteSocketClient::class
    ];

    /**
     * @return RemoteSocketClient
     */
    public function create()
    {
        $class = $this->getExceptedConfigValue('class', RemoteSocketClient::class);

        $socketClientCreator = self::getNextNode($this, RemoteStrategySocketCreator::class, 'socket');
        $socketClient = $socketClientCreator->create();

        /** @var RemoteSocketClient $client */
        return new $class($socketClient);
    }
}