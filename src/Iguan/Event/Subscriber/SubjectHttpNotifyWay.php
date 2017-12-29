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

    public function getIncomingSerializedEvents()
    {
        if (!isset($_SERVER['X-Iguan'])) return '';

        return file_get_contents('php://input');
    }

    /**
     * @return CommonAuth
     */
    public function getIncomingAuth()
    {
        return new CommonAuth(
            isset($_SERVER[$this->tokenHeader]) ? $_SERVER[$this->tokenHeader] : '',
            isset($_SERVER[$this->tokenNameHeader]) ? $_SERVER[$this->tokenNameHeader] : ''
        );
    }

    public function getNotifyWayType()
    {
        return self::TYPE;
    }

    public function getNotifyWayExtra()
    {
        return $this->url;
    }

    public function hashCode()
    {
        return md5($this->url);
    }
}