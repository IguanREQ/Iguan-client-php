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
    private $signArgNumber;
    /**
     * @var int
     */
    private $tokenNameArgNumber;

    public function __construct($script, $eventsArgNumber = 1, $signArgNumber = 2)
    {
        $this->script = $script;
        $this->eventsArgNumber = $eventsArgNumber;
        $this->signArgNumber = $signArgNumber;
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

    /**
     * Get data piece signed by trusted source.
     *
     * @return string
     */
    public function getSignedContextData()
    {
        return __FILE__ . $this->getIncomingSerializedEvents();
    }

    /**
     * Get trusted source sign from header
     *
     * @return string
     */
    public function getSign()
    {
        global $argv;

        return isset($argv[$this->signArgNumber]) ? $argv[$this->signArgNumber] : '';
    }
}