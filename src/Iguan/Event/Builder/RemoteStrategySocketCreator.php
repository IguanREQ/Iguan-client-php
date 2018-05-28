<?php
namespace Iguan\Event\Builder;


use Iguan\Common\Remote\SocketClient;

/**
 * Class RemoteStrategySocketCreator
 * Creator for 'common.remote.client.socket' key.
 *
 * @author Vishnevskiy Kirill
 */
class RemoteStrategySocketCreator extends Creator
{
    const EXCEPTED_CONFIG_VALUES = [
        'protocol' => [
            'values' => ['tcp', 'tls', 'ssl'],
            'types' => ['string'],
        ],
        'ssl_cert_path' => [
            'types' => ['string'],
        ],
        'timeout_s' => [
            'types' => ['integer'],
        ],
        'timeout_ms' => [
            'types' => ['integer'],
        ],
        'persist' => [
            'types' => ['boolean'],
        ],
        'class' => SocketClient::class
    ];

    /**
     * @return SocketClient
     */
    public function create()
    {
        $protocol = $this->getExceptedConfigValue('protocol', 'tcp');
        $host = $this->getExceptedConfigValue('host', '127.0.0.1');
        $port = $this->getExceptedConfigValue('port', '11133');
        $ssl_cert_path = $this->getExceptedConfigValue('ssl_cert_path', '');
        $timeout_s = $this->getExceptedConfigValue('timeout_s', 2);
        $timeout_ms = $this->getExceptedConfigValue('timeout_ms', 0);
        $persist = $this->getExceptedConfigValue('persist', false);
        $class = $this->getExceptedConfigValue('class', SocketClient::class);

        /** @var SocketClient $client */
        $client = new $class($protocol . '://' . $host . ':' . $port);
        if (!empty($ssl_cert_path)) {
            $client->setCertificatePath($ssl_cert_path);
        }
        $client->setTimeout($timeout_s, $timeout_ms);
        if ($persist) {
            $client->persist();
        }

        return $client;
    }
}