<?php

namespace Iguan\Common\Util;

use ReflectionObject;

/**
 * Class Variable.
 * An util class for casting variables between types.
 *
 * @author Vishnevskiy Kirill
 */
class Variable
{
    /**
     * @param mixed $var be tested for type
     * @return string type or class of variable
     */
    public static function getTrueType($var)
    {
        $type = gettype($var);
        if ($type === 'object') $type = get_class($var);
        return $type;
    }

    /**
     * @param mixed $var be casted
     * @param string $type a result of Variable::getTrueType call
     * @return mixed casted value. If $type was an existing class
     * method will return an instance of that class.
     */
    public static function cast($var, $type)
    {
        switch ($type) {
            case 'boolean':
                $var = (bool)$var;
                break;
            case 'integer':
                $var = (int)$var;
                break;
            case 'double':
                $var = (double)$var;
                break;
            case 'string':
                $var = (string)$var;
                break;
            case 'array':
                $var = (array)$var;
                break;
            case 'resource':
            case 'resource (closed)':
            case 'NULL':
                $var = null;
                break;
            default:
                if (class_exists($type)) {
                    $destination = new $type();
                    $sourceReflection = new ReflectionObject($var);
                    $destinationReflection = new ReflectionObject($destination);
                    $sourceProperties = $sourceReflection->getProperties();
                    foreach ($sourceProperties as $sourceProperty) {
                        $sourceProperty->setAccessible(true);
                        $name = $sourceProperty->getName();
                        $value = $sourceProperty->getValue($var);
                        if ($destinationReflection->hasProperty($name)) {
                            $propDest = $destinationReflection->getProperty($name);
                            $propDest->setAccessible(true);
                            $propDest->setValue($destination, $value);
                        } else {
                            $destination->$name = $value;
                        }
                    }
                    $var = $destination;
                }
        }
        return $var;
    }
}