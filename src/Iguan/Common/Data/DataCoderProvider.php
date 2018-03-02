<?php

namespace Iguan\Common\Data;

/**
 * Class DataDecoderProvider
 * Creator for default realization of different
 * coders.
 *
 * @author Vishnevskiy Kirill
 */
class DataCoderProvider
{
    const DECODER_INDEX = 'decoder';
    const ENCODER_INDEX = 'encoder';

    const FORMAT_CODER_MAP = [
        'yaml' => ['decoder' => [YamlDataDecoder::class], 'encoder' => [YamlDataEncoder::class]],
        'yml' => ['decoder' => [YamlDataDecoder::class], 'encoder' => [YamlDataEncoder::class]],
        'json' => ['decoder' => [JsonDataDecoder::class, true], 'encoder' => [JsonDataEncoder::class]]
    ];

    /**
     * Get a decoder for passed format.
     *
     * @param string $format required data format with need to be decoded
     * @return DataDecoder
     */
    public static function getDecoderForFormat($format)
    {
        $coder = self::getCoderForFormat($format, self::DECODER_INDEX);
        if ($coder !== null) return $coder;

        throw new UnknownFormatException("Format \"$format\" is unknown. Supported formats: " . self::getSupportedExtensions() . ".");
    }

    /**
     * Get encoder to encode if passed format
     * @param string $format required data format
     * @return DataEncoder
     */
    public static function getEncoderForFormat($format)
    {
        $coder = self::getCoderForFormat($format, self::ENCODER_INDEX);
        if ($coder !== null) return $coder;

        throw new UnknownFormatException("Format \"$format\" is unknown. Supported formats: " . self::getSupportedExtensions() . ".");
    }

    private static function getCoderForFormat($format, $coderIndex)
    {
        $format = strtolower($format);
        if (isset(self::FORMAT_CODER_MAP[$format]) && isset(self::FORMAT_CODER_MAP[$format][$coderIndex])) {
            $class = self::FORMAT_CODER_MAP[$format][$coderIndex][0];
            $arguments = array_slice(self::FORMAT_CODER_MAP[$format][$coderIndex], 1);
            return new $class(...$arguments);
        }

        return null;
    }

    /**
     * Get data decoder for file.
     *
     * @param string $file to be decoded.
     * @return DataDecoder
     */
    public static function getDecoderForFile($file)
    {
        return self::getCoderForFile($file, self::DECODER_INDEX);
    }

    private static function getCoderForFile($file, $coderIndex)
    {
        if (!file_exists($file)) throw new \RuntimeException("File \"$file\" does not exist or unavailable.");

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (empty($extension)) {
            throw new UnknownFormatException("File \"$file\" has no extension. Specify one of supported: " . self::getSupportedExtensions() . '.');
        }
        $coder = self::getCoderForFormat($extension, $coderIndex);
        if ($coder !== null) return $coder;

        throw new UnknownFormatException("File \"$file\" format is unknown. Supported extensions: " . self::getSupportedExtensions() . ".");
    }

    private static function getSupportedExtensions()
    {
        return implode(', ', array_keys(self::FORMAT_CODER_MAP));
    }
}