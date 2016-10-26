<?php
namespace Garden;

/**
 * Framework superobject
 *
 */
class Gdn extends Plugin {

    /**
     * @param string $name
     * @param array|null $config
     * @return Db\Database
     */
    public static function database($name = null, array $config = null)
    {
        return Db\Database::instance($name, $config);
    }

    /**
     * @param string $name
     * @return DB\Structure
     */
    public static function structure($name = false)
    {
        return Db\Structure::instance($name);
    }

    /**
     * @return Application
     */
    public static function app()
    {
        return self::factory('Garden\\Application');
    }

    /**
     * @param string $driver
     * @return Cache
     */
    public static function cache($driver = null)
    {
        return Cache::instance($driver);
    }

    /**
     * @return Cache\Dirty
     */
    public static function dirtyCache()
    {
        return Cache::instance('dirty');
    }

    /**
     * @return Session
     */
    public static function session()
    {
        return Session::instance();
    }

    public static function response()
    {
        return Response::current();
    }

    public static function request()
    {
        return Request::current();
    }

    public static $translations = [];
    /**
     * @param string $code
     * @param string $default
     * @return string
     */
    public static function translate($code, $default = null)
    {
        if (substr($code, 0, 1) === '@') {
            return substr($code, 1);
        } elseif (isset(self::$translations[$code])) {
            return self::$translations[$code];
        } elseif ($default !== null) {
            return $default;
        } else {
            return $code;
        }
    }

}