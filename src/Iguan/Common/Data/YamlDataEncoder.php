<?php
namespace Iguan\Common\Data;

use Exception;


/**
 * Class YamlDataEncoder.
 * Encode data into YAML format.
 *
 * @author Vishnevskiy Kirill
 */
class YamlDataEncoder extends DataEncoder
{

    /**
     * A method for do a data encode work.
     *
     * @param $data mixed a structure need to be encoded
     * @throws EncodeDecodeException if there is an errors during encoding
     * @return string an encoded string
     */
    public function encode($data)
    {
        try {
            return \Spyc::YAMLDump($data);
        } catch (Exception $e) {
            throw new EncodeDecodeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}