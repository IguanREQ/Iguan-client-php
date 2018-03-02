<?php
namespace Iguan\Event\Builder;

/**
 * Class InvalidConfigValue
 * For config-time errors.
 *
 * @author Vishnevskiy Kirill
 */
class InvalidConfigValueException extends \RuntimeException
{
    public static function fromValidArray($key, $value, $valids) {
        return new InvalidConfigValueException(
            'Value "' . $value . '" for "' . $key . '" are invalid. Supported values: ["' . implode('", "', $valids) . '"].'
        );
    }

    public static function fromValidTypes($key, $value, $types) {
        return new InvalidConfigValueException(
            'Type of value "' . $value . '" for "' . $key . '" are invalid. Supported values: ["' . implode('", "', $types) . '"]. '
            . '"' . gettype($value) . '" given.'
        );
    }

    public static function fromValidSubclass($key, $value, $valid) {
        return new InvalidConfigValueException(
            'Object for "' . $key . '" must be subclass of "' . $valid . '". "' . $value . '" given.'
        );
    }

    public static function fromNonExistenceClass($key, $value) {
        return new InvalidConfigValueException(
            'Class "' . $value . '" for "' . $key . '" key does not exist.'
        );
    }
}