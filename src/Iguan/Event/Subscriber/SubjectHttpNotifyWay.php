<?php
namespace Iguan\Event\Subscriber;
use Iguan\Event\Common\CommonAuth;

/**
 * Class SubjectHttpNotifyWay
 *
 * @author Vishnevskiy Kirill
 */
class SubjectHttpNotifyWay extends SubjectNotifyWay
{
    const TYPE = 2;

    private $url;
    /**
     * @var string
     */
    private $tokenHeader;
    /**
     * @var string
     */
    private $tokenNameHeader;

    public function __construct($url, $tokenHeader = 'X-Iguan-Token', $tokenNameHeader = 'X-Iguan-Token-Name')
    {

        $this->url = $url;
        $this->tokenHeader = $tokenHeader;
        $this->tokenNameHeader = $tokenNameHeader;
    }

    /**
     * Fetch serialized incoming data from globals.
     * Data will be extracted from STDIN if 'X-Iguan'
     * are present.
     *
     * @return string
     */
    public function getIncomingSerializedEvents()
    {
        if (!isset($_SERVER['X-Iguan'])) return '';

        return file_get_contents('php://input');
    }

    /**
     * Fetch auth data from globals.
     * Auth will be extracted from incoming headers
     * using current headers keys.
     *
     * @return CommonAuth
     */
    public function getIncomingAuth()
    {
        return new CommonAuth(
            isset($_SERVER[$this->tokenHeader]) ? $_SERVER[$this->tokenHeader] : '',
            isset($_SERVER[$this->tokenNameHeader]) ? $_SERVER[$this->tokenNameHeader] : ''
        );
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
        return $this->url;
    }

    /**
     * Hash of way instance.
     * Different ways must returns different ways.
     * This is MD5 from current URL.
     *
     * @return string
     */
    public function hashCode()
    {
        return md5($this->url);
    }
}