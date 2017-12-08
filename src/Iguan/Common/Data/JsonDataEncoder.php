<?php

namespace Iguan\Common\Data;

/**
 * Class JsonDataEncoder
 * @author Vishnevskiy Kirill
 */
class JsonDataEncoder extends DataEncoder
{
    /**
     * @var int
     */
    private $options;
    /**
     * @var int
     */
    private $depth;

    /**
     * Given arguments will ve passed to json_encode without
     * changes with saved order.
     *
     * @param int $options encoding options (@see http://php.net/manual/en/function.json-encode.php)
     * @param int $depth max encoding depth
     */
    public function __construct($options = 0, $depth = 512)
    {
        $this->options = $options;
        $this->depth = $depth;
    }

    /**
     * Encode data ti JSON string according to current
     * options state.
     *
     * @param mixed $data to be encoded. Any, exclude a resource
     * @return string JSON string
     * @throws JsonException in case of error
     */
    public function encode($data)
    {
        $encoded = json_encode($data, $this->options, $this->depth);
        if (JsonException::hasError()) {
            throw new JsonException();
        }

        return $encoded;
    }
}