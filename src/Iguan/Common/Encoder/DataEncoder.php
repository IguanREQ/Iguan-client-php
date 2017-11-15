<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 22.10.2017
 * Time: 0:35
 */

namespace Internal\Util\Encoder;

/**
 * Class DataEncoder
 * @author Vishnevskiy Kirill
 */
abstract class DataEncoder
{
    /**
     * A method for do a data encode work.
     *
     * @param $data string a structure need to be encoded
     * @throws EncodeDecodeException if there is an errors during encoding
     * @return string an encoded string
     */
    public abstract function encode($data);
}