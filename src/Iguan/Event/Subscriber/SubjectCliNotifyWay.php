<?php

namespace Iguan\Event\Subscriber;


use Iguan\Event\Common\CommonAuth;

/**
 * Class SubjectCliNotifyWay
 *
 * @author Vishnevskiy Kirill
 */
class SubjectCliNotifyWay extends SubjectNotifyWay
{
    const TYPE = 1;
    private $script;
    /**
     * @var int
     */
    private $eventsArgNumber;
    /**
     * @var int
     */
    private $tokenArgNumber;
    /**
     * @var int
     */
    private $tokenNameArgNumber;

    public function __construct($script, $eventsArgNumber = 1, $tokenArgNumber = 2, $tokenNameArgNumber = 3)
    {
        $this->script = $script;
        $this->eventsArgNumber = $eventsArgNumber;
        $this->tokenArgNumber = $tokenArgNumber;
        $this->tokenNameArgNumber = $tokenNameArgNumber;
    }

    public function getIncomingSerializedEvents()
    {
        global $argv;

        $str = isset($argv[$this->eventsArgNumber]) ? base64_decode($argv[$this->eventsArgNumber]) : '';
        return $str;
    }

    public function getNotifyWayType()
    {
        return self::TYPE;
    }

    public function getNotifyWayExtra()
    {
        return $this->script;
    }

    public function getIncomingAuth()
    {
        global $argv;

        return new CommonAuth(
            isset($argv[$this->tokenArgNumber]) ? $argv[$this->tokenArgNumber] : '',
            isset($argv[$this->tokenNameArgNumber]) ? $argv[$this->tokenNameArgNumber] : ''
        );
    }

    public function hashCode()
    {
        return hash('md5', $this->script);
    }
}