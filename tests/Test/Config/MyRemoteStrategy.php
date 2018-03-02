<?php
/**
 * Created by PhpStorm.
 * User: 119
 * Date: 01.03.2018
 * Time: 18:08
 */

namespace Test\Config;


use Iguan\Common\Data\DataDecoder;
use Iguan\Common\Data\DataEncoder;
use Iguan\Event\Common\Remote\RemoteClient;
use Iguan\Event\Common\Remote\RemoteCommunicateStrategy;

class MyRemoteStrategy extends RemoteCommunicateStrategy
{
    /**
     * @var RemoteClient
     */
    private $remoteClient;
    private $waitForAnswer;
    /**
     * @var DataEncoder
     */
    private $encoder;
    /**
     * @var DataDecoder
     */
    private $decoder;

    public function __construct(RemoteClient $remoteClient, DataEncoder $encoder = null, DataDecoder $decoder = null)
    {
        parent::__construct($remoteClient, $encoder, $decoder);
        $this->remoteClient = $remoteClient;
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    public function setWaitForAnswer($waitForAnswer)
    {
        parent::setWaitForAnswer($waitForAnswer);
        $this->waitForAnswer = $waitForAnswer;
    }

    public function isWaitForAnswer() {
        return $this->waitForAnswer;
    }


    /**
     * @return RemoteClient
     */
    public function getRemoteClient()
    {
        return $this->remoteClient;
    }

    /**
     * @return DataDecoder
     */
    public function getDecoder()
    {
        return $this->decoder;
    }

    /**
     * @return DataEncoder
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    public function getAuth()
    {
        return parent::getAuth(); // TODO: Change the autogenerated stub
    }
}