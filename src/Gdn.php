<?php
namespace Garden;

/**
 * Framework superobject
 *
 */
class Gdn {
    protected static $instances;

    public static function database() {
        return self::factory('Database');
    }

    public static function factory($className) {
        $args = func_get_args();
        array_shift($args);
        $hash = self::factoryHash($className, $args);
        
        if (!isset(self::$instances[$hash])) {
            self::$instances[$hash] = self::_instantiateObject($className, $args);
        }

        return self::$instances[$hash];
    }

    protected static function factoryHash($className, $args = array()) {
        return empty($args) ? $className : md5($className.json_encode($args));
    }

    /** 
     * Instantiate a new object.
     *
     * @param string $className The name of the class to instantiate.
     * @param array $args The arguments to pass to the constructor.
     * Note: This function currently only supports a maximum of 8 arguments.
     */
    protected static function _instantiateObject($className, $args = array()) {
        $result = NULL;

        //check namespace
        $path = explode('\\', $className);
        if(count($path) <= 1) {
            $className = 'Garden\\'.$className; 
        }

        // Instantiate the object with the correct arguments.
        // This odd looking case statement is purely for speed optimization.
        switch(count($args)) {
            case 0:
                $result = new $className; break;
            case 1:
                $result = new $className($args[0]); break;
            case 2:
                $result = new $className($args[0], $args[1]); break;
            case 3:
                $result = new $className($args[0], $args[1], $args[2]); break;
            case 4:
                $result = new $className($args[0], $args[1], $args[2], $args[3]); break;
            case 5:
                $result = new $className($args[0], $args[1], $args[2], $args[3], $args[4]); break;
            case 6:
                $result = new $className($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]); break;
            case 7:
                $result = new $className($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]); break;
            case 8:
                $result = new $className($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]); break;
            default:
                throw new Exception();
        }

        return $result;
    }

    public static function exists($className) {
        $args = func_get_args();
        array_shift($args);
        $hash = self::factoryHash($className, $args);
        return isset(self::$instances[$hash]);
    }
}