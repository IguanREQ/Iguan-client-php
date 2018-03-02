<?php
namespace Iguan\Common\Util;

/**
 * Interface ReadWriter
 * Some read-writer interface.
 *
 * @author Vishnevskiy Kirill
 */
interface ReadWriter
{
    /**
     * Get a value.
     *
     * @param string $key for value lookup
     * @param mixed $defaultValue which will be returned when key is not found
     * @return mixed a key value or $defaultValue
     */
    public function getValue($key, $defaultValue = null);

    /**
     * Associate a value with key.
     *
     * @param string $key for storing value
     * @param mixed $value a value for key
     */
    public function setValue($key, $value);
}