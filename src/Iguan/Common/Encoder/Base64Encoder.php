<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 01.11.2017
 * Time: 0:31
 */

namespace Internal\Util\Encoder;

/**
 * Class Base64Encoder
 * @author Vishnevskiy Kirill
 */
class Base64Encoder extends DataEncoder
{

    /**
     * A method for do a data encode work.
     *
     * @param $data string a structure need to be encoded
     * @throws EncodeDecodeException if there is an errors during encoding
     * @return string an encoded base64 string
     */
    public function encode($data)
    {
        if (!is_string($data)) throw new Base64Exception('Incoming data must be string. ' . gettype($data) . ' given.');

        return base64_encode($data);
    }
}