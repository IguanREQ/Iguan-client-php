<?php

namespace Test\Dispatcher;

use Iguan\Common\Encoder\DataEncoder;

/**
 * Class NoDataEncoder
 * @author Vishnevskiy Kirill
 */
class NoDataEncoder extends DataEncoder
{

    /**
     * A method for do a data encode work.
     *
     * @param $data string a structure need to be encoded
     * @throws \Iguan\Common\Encoder\EncodeDecodeException if there is an errors during encoding
     * @return string an encoded string
     */
    public function encode($data)
    {
        return '';
    }
}