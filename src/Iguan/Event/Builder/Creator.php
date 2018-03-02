<?php

namespace Iguan\Event\Builder;

/**
 * Class Creator.
 * Creator create instance of some object
 * using config values for current node.
 *
 * @author Vishnevskiy Kirill
 */
abstract class Creator
{
    const EXCEPTED_CONFIG_VALUES = [];

    /**
     * @var Config
     */
    private $config;
    private $nodeRoot;

    /**
     * Creator constructor.
     * @param Config $config common config
     * @param string $nodeRoot which indicate a level for current creator.
     */
    public function __construct(Config $config, $nodeRoot)
    {
        $this->config = $config;
        $this->nodeRoot = $nodeRoot;
    }

    /**
     * Instantiate new creator for next node.
     *
     * @param Creator $that context node
     * @param string $creatorClass new creator class
     * @param string $key node subset
     * @return Creator
     */
    protected static function getNextNode(Creator $that, $creatorClass, $key) {
        return new $creatorClass($that->config, $that->nodeRoot . '.' . $key);
    }

    /**
     * Create an instance for config subset.
     *
     * @return mixed
     */
    abstract public function create();


    /**
     * Get config value in safe-mode and check restrictions
     * for value. If restriction not met, there will be an error.
     * Config restriction are stored in EXCEPTED_CONFIG_VALUES class constant,
     * where key is a config key and value is an array with two keys:
     *     'values' - allowable values for config value
     *     'types' - allowable types for config value
     * If key equals 'class' - a value will be checked for
     * matching (subclass of) with excepted class.
     *
     * @param string $key a key for current node
     * @param mixed $default value if key does not exist
     * @return mixed
     */
    protected function getExceptedConfigValue($key, $default = null) {
        $fullKey = $this->getFullKey($key);

        $value = $this->config->getValue($fullKey, $default);

        //we have some restrictions for this value
        if (array_key_exists($key, static::EXCEPTED_CONFIG_VALUES)) {
            $this->checkConfigValues($key, $value);
            $this->checkConfigTypes($key, $value);
            $this->checkConfigClass($key, $value);
        }

        return $value;
    }

    /**
     * Compose full path for getting value from source config.
     *
     * @param string $key current node key.
     * @return string
     */
    private function getFullKey($key) {
        return $this->nodeRoot . '.' . $key;
    }


    private function checkConfigValues($key, $value) {
        if (isset(static::EXCEPTED_CONFIG_VALUES[$key]['values']) && is_array(static::EXCEPTED_CONFIG_VALUES[$key]['values'])) {
            $this->checkValueIsExceptedArray($key, $value, static::EXCEPTED_CONFIG_VALUES[$key]['values']);
        }
    }

    private function checkConfigTypes($key, $value) {
        if (isset(static::EXCEPTED_CONFIG_VALUES[$key]['types'])) {
            $types = static::EXCEPTED_CONFIG_VALUES[$key]['types'];
            if (is_string($types)) {
                $types = [$types];
            }

            $this->checkValueIsExceptedTypes($key, $value, $types);
        }
    }

    private function checkConfigClass($key, $value) {
        if ($key === 'class') {
            $this->checkValueIsSubclassOf($key, $value, static::EXCEPTED_CONFIG_VALUES[$key]);
        }
    }

    private function checkValueIsExceptedArray($key, $value, $exceptedValues) {
        if (!in_array($value, $exceptedValues)) {
            $fullKey = $this->getFullKey($key);
            throw InvalidConfigValueException::fromValidArray($fullKey, $value, $exceptedValues);
        }
    }

    private function checkValueIsExceptedTypes($key, $value, $exceptedTypes) {
        if (!in_array(gettype($value), $exceptedTypes)) {
            $fullKey = $this->getFullKey($key);
            throw InvalidConfigValueException::fromValidTypes($fullKey, $value, $exceptedTypes);
        }
    }

    private function checkValueIsSubclassOf($key, $value, $exceptedClass) {
        $fullKey = $this->getFullKey($key);
        if (!class_exists($value)) {
            throw InvalidConfigValueException::fromNonExistenceClass($fullKey, $value);
        }

        if (!($value === $exceptedClass || is_subclass_of($value, $exceptedClass))) {
            throw InvalidConfigValueException::fromValidSubclass($fullKey, $value, $exceptedClass);
        }

    }
}