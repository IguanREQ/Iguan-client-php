<?php
/**
 * Created by PhpStorm.
 * User: 119
 * Date: 01.03.2018
 * Time: 18:19
 */

namespace Test\Config;


use Iguan\Common\Remote\SocketClient;
use Iguan\Event\Common\Remote\RemoteSocketClient;

class MyRemoteClient extends RemoteSocketClient
{

    /**
     * @var SocketClient
     */
    private $client;

    public function __construct(SocketClient $client)
    {
        parent::__construct($client);
        $this->client = $client;
    }

    /**
     * @return SocketClient
     */
    public function getClient()
    {
        return $this->client;
    }
}