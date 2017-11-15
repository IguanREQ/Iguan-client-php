<?php
/**
 * Created by PhpStorm.
 * User: viirr
 * Date: 22.10.2017
 * Time: 1:05
 */

namespace Internal\Util\Encoder;
use Exception;

/**
 * Class DataDecoder
 * @author Vishnevskiy Kirill
 */
abstract class DataDecoder
{
    /**
     * A method for do a data decode work.
     *
     * @param $data string an encoded data need to be decoded
     * @throws EncodeDecodeException if there is an errors during decoding
     * @return mixed a decoded structure
     */
    public abstract function decode($data);
}