<?php

namespace Iguan\Common\Data;

/**
 * Class DataEncoder
 * @author Vishnevskiy Kirill
 */
abstract class DataEncoder
{
    /**
     * A method for do a data encode work.
     *
     * @param $data mixed a structure need to be encoded
     * @throws EncodeDecodeException if there is an errors during encoding
     * @return string an encoded string
     */
    public abstract function encode($data);
}