<?php

namespace Iguan\Common\Data;

/**
 * Class Base64Decoder
 * @author Vishnevskiy Kirill
 */
class Base64Decoder extends DataDecoder
{
    /**
     * @var bool
     */
    private $use_strict;

    public function __construct($use_strict = null)
    {
        $this->use_strict = $use_strict;
    }

    /**
     * A method for do a data decode work.
     *
     * @param $data string a base64 encoded data need to be decoded
     * @throws Base64Exception if there is an errors during decoding
     * @return mixed a decoded structure
     */
    public function decode($data)
    {
        if (!is_string($data)) throw new Base64Exception('Incoming data must be string. ' . gettype($data) . ' given.');

        $result = base64_decode($data, $this->use_strict);
        if ($result === false) throw new Base64Exception('Cannot to decode incoming string. Perhaps, string contain characters outside base64 set.');
        return $result;
    }
}