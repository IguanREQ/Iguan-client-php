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

    /**
     * Fetch serialized incoming data from globals.
     * Data will be extracted from CLI argument.
     * Incoming data must be encoded in base64 format.
     *
     * @return string
     */
    public function getIncomingSerializedEvents()
    {
        global $argv;

        $str = isset($argv[$this->eventsArgNumber]) ? base64_decode($argv[$this->eventsArgNumber]) : '';
        return $str;
    }

    /**
     * Get a way identifier.
     *
     * @return int
     */
    public function getNotifyWayType()
    {
        return self::TYPE;
    }

    /**
     * Get a way extra data, i.e. file path or
     * remote script location.
     *
     * @return string
     */
    public function getNotifyWayExtra()
    {
        return $this->script;
    }

    /**
     * Fetch auth data from globals.
     * Auth will be extracted from CLI input arguments.
     *
     * @return CommonAuth
     */
    public function getIncomingAuth()
    {
        global $argv;

        return new CommonAuth(
            isset($argv[$this->tokenArgNumber]) ? $argv[$this->tokenArgNumber] : '',
            isset($argv[$this->tokenNameArgNumber]) ? $argv[$this->tokenNameArgNumber] : ''
        );
    }

    /**
     * Hash of way instance.
     * Different ways must returns different ways.
     * This is MD5 from current script path.
     *
     * @return string
     */
    public function hashCode()
    {
        return hash('md5', $this->script);
    }
}