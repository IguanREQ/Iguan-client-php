<?php
namespace Iguan\Event\Subscriber;

use Iguan\Common\Data\Base64Decoder;
use Iguan\Common\Data\Base64Exception;
use Iguan\Event\Common\CommonAuth;

/**
 * Class SubjectHttpNotifyWay
 *
 * @author Vishnevskiy Kirill
 */
class SubjectHttpNotifyWay extends SubjectNotifyWay
{
    const TYPE = 2;

    /** @var UriPair */
    private $uriPair;
    /**
     * @var string
     */
    private $signHeader;
    /**
     * @var string
     */
    private $destHostHeader;

    private $eventsInput;

    public function __construct(UriPair $uriPair, $signHeader = 'Iguan-Sign', $destHostHeader = 'Iguan-Dest-Host')
    {
        $this->uriPair = $uriPair;
        $this->signHeader = $signHeader;
        $this->destHostHeader = $destHostHeader;
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

        if ($this->eventsInput === null) {
            $this->eventsInput = file_get_contents('php://input');
        }

        return $this->eventsInput;
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
     * @return UriPair
     */
    public function getNotifyWayExtra()
    {
        return $this->uriPair;
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
        return md5($this->uriPair->fluentPart . $this->uriPair->appPart);
    }

    /**
     * Get trusted source sign from header in Base64
     *
     * @return string
     */
    public function getSign()
    {
        try {
            return (new Base64Decoder())->decode($_SERVER[$this->signHeader]);
        } catch (Base64Exception $e) {
            return '';
        }
    }

    /**
     * Get data piece signed by trusted source.
     * It is Iguan-Dest header followed by body content.
     *
     * @return string
     */
    public function getSignedContextData()
    {
        return $_SERVER[$this->destHostHeader] . $this->getIncomingSerializedEvents();
    }
}