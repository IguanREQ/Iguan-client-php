<?php
namespace Iguan\Event\Builder;

use Iguan\Common\Data\DataCoderProvider;
use Iguan\Common\Data\EncodeDecodeException;
use Iguan\Common\Util\DotArrayAccessor;
use Iguan\Common\Util\ReadWriter;


/**
 * Class Config.
 * A top-level wrapper over some data storage.
 *
 * @author Vishnevskiy Kirill
 */
class Config
{
    /**
     * @var ReadWriter
     */
    private $readWriter;

    /**
     * Config constructor.
     *
     * @param ReadWriter $readWriter from (to) which read (write) config values
     */
    public function __construct(ReadWriter $readWriter)
    {
        $this->readWriter = $readWriter;
    }

    /**
     * @param string $key of config
     * @param null $defaultValue if $key is not present in config
     * @return mixed config value
     */
    public function getValue($key, $defaultValue = null)
    {
        return $this->readWriter->getValue($key, $defaultValue);
    }

    /**
     * @param string $key of config
     * @param mixed $value for $key
     */
    public function setValue($key, $value)
    {
        $this->readWriter->setValue($key, $value);
    }

    /**
     * Create config accessor from file.
     *
     * @param string $filePath to config
     * @return Config
     */
    public static function fromFile($filePath)
    {
        $decoder = DataCoderProvider::getDecoderForFile($filePath);

        try {
            $data = $decoder->decode(file_get_contents($filePath));

            return new Config(new DotArrayAccessor($data));
        } catch (EncodeDecodeException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}