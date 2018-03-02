<?php
namespace Iguan\Common\Data;

use PHPUnit\Runner\Exception;

/**
 * Class YamlDataEncoder.
 * Decode data from YAML format.
 *
 * @author Vishnevskiy Kirill
 */
class YamlDataDecoder extends DataDecoder
{

    /**
     * A method for do a data decode work.
     *
     * @param $data string an encoded data need to be decoded
     * @throws EncodeDecodeException if there is an errors during decoding
     * @return mixed a decoded structure
     */
    public function decode($data)
    {
        try {
            return \Spyc::YAMLLoadString($data);
        } catch (Exception $e) {
            throw new EncodeDecodeException($e->getMessage(), $e->getCode(), $e);
        }
    }

}