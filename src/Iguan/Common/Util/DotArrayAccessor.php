<?php

namespace Iguan\Common\Util;


/**
 * Class DotArrayAccessor implement interface
 * for accessing to array elements using "dot" notation.
 * For example, we have an array:
 *     $a = ['node' => ['node' => ['leaf' => 'It's leaf!']]]
 * Now, for getting 'It's leaf!' value,
 * we have to use key like 'node.node.leaf'
 * instead of getting value like $a['node']['node']['leaf'].
 * Plus, if one of key is not found in chain,
 * there will not be an error.
 *
 * @author Vishnevskiy Kirill
 */
class DotArrayAccessor implements ReadWriter
{

    /**
     * @var array
     */
    private $sourceData;

    /**
     * DotArrayAccessor constructor.
     *
     * @param array $data source array to be used
     */
    public function __construct(array $data = [])
    {
        $this->sourceData = $data;
    }

    /**
     * @param string $key a key in "dot" notation to retrieve value
     * @param mixed $defaultValue to be returned if $key is not found
     * @return mixed an array value or $defaultValue
     */
    public function getValue($key, $defaultValue = null)
    {
        return $this->readValue($this->sourceData, $key, $defaultValue);
    }

    /**
     * Recursively lookup for key over node until
     * key has a "dot".
     *
     * @param array $node for lookup key
     * @param string $key
     * @param mixed $defaultValue if key does not exist
     * @return mixed a value for key or $defaultValue
     */
    private function readValue($node, $key, $defaultValue)
    {
        if (!is_array($node)) return $defaultValue;

        if (array_key_exists($key, $node)) return $node[$key];

        $firstDotPos = strpos($key, '.');
        if ($firstDotPos === false) return $defaultValue;

        $nodeKey = substr($key, 0, $firstDotPos);
        $nextKey = substr($key, $firstDotPos + 1);

        if (!array_key_exists($nodeKey, $node)) return $defaultValue;

        return $this->readValue($node[$nodeKey], $nextKey, $defaultValue);
    }

    /**
     * @param string $key a key in "dot" notation under which value will be stored
     * @param mixed $value value to be stored
     */
    public function setValue($key, $value) {
        $this->writeValue($this->sourceData, $key, $value);
    }

    /**
     * Recursively getting leaf for key.
     * If some node in key does not exist -
     * it will be created with empty array.
     *
     * @param array $node
     * @param string $key
     * @param mixed $value
     */
    private function writeValue(&$node, $key, $value) {
        $firstDotPos = strpos($key, '.');
        if ($firstDotPos === false) {
            $node[$key] = $value;
            return;
        }

        $nodeKey = substr($key, 0, $firstDotPos);
        $nextKey = substr($key, $firstDotPos + 1);

        if (!array_key_exists($nextKey, $node)) {
            $node[$nextKey] = [];
        }

        $this->writeValue($node[$nodeKey], $nextKey, $value);
    }

    /**
     * Get a sub-node.
     * If $key is not found, sub accessor will be an empty.
     *
     * @param string $key sub-node key
     * @return DotArrayAccessor
     */
    public function slice($key) {
        return new DotArrayAccessor($this->getValue($key, []));
    }

}