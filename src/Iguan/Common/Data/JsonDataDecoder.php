<?php


namespace Iguan\Common\Data;

/**
 * Class JsonDataDecoder
 * @author Vishnevskiy Kirill
 */
class JsonDataDecoder extends DataDecoder
{
    /**
     * @var bool
     */
    private $assoc;
    /**
     * @var int
     */
    private $depth;
    /**
     * @var int
     */
    private $options;


    /**
     * Given arguments will ve passed to json_decode without
     * changes with saved order.
     *
     * @param bool $assoc if true, returned value will be
     *             an assoc array instead of std object
     * @param int $depth max encoding depth
     * @param int $options decoding options (см http://php.net/manual/ru/function.json-decode.php)
     */
    public function __construct($assoc = false, $depth = 512, $options = 0)
    {
        $this->assoc = $assoc;
        $this->depth = $depth;
        $this->options = $options;
    }

    /**
     * Decode a JSON string according to current
     * options state.
     *
     * @param $data string JSON encoded data
     * @return mixed decoded structure
     * @throws JsonException in case of decode error
     */
    public function decode($data)
    {
        $decoded_data = json_decode($data, $this->assoc, $this->depth, $this->options);

        if (JsonException::hasError()) {
            throw new JsonException();
        }

        return $decoded_data;
    }


}