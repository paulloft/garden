<?php
namespace Garden\Cache;
use \Garden\Exception\UserException;
/**
* 
*/
abstract class Cache extends \Garden\Plugin
{
    const DEFAULT_LIFETIME = 3600;

    /**
     * @var   string     default driver to use
     */
    public static $default = 'file';

    /**
     * @var   Cache instances
     */
    public static $instances = array();

    public static $enabled = true;

    public static function instance($driver = null)
    {
        $options = c('main.cache');
        self::$enabled = val('enabled', $options);

        if(!self::$enabled) {
            $driver = 'dirty';
        } elseif(!$driver) {
            $driver = val('driver', $options, self::$default);
        }

        if (isset(self::$instances[$type])) {
            return self::$instances[$type];
        }

        $driverClass = 'Garden\Cache\Driver\\'.ucfirst($driver);

        if(!class_exists($driverClass)) {
            throw new UserException("Cache driver \"%s\" not found", array($driver));
        } else {
            $config = c("cache.$driver");
            self::$instances[$type] = new $driverClass($config);
        }

        return self::$instances[$type];
    }


    /**
     * Retrieve a cached value entry by id.
     *
     * @param   string  $id       id of cache to entry
     * @param   string  $default  default value to return if cache miss
     * @return  mixed
     * @throws  Cache_Exception
     */
    abstract public function get($id, $default = null);

    /**
     * Set a value to cache with id and lifetime
     *
     * @param   string   $id        id of cache entry
     * @param   string   $data      data to set to cache
     * @param   integer  $lifetime  lifetime in seconds
     * @return  boolean
     */
    abstract public function set($id, $data, $lifetime = 3600);

    /**
     * Delete a cache entry based on id
     *
     * @param   string  $id  id to remove from cache
     * @return  boolean
     */
    abstract public function delete($id);

    /**
     * Delete all cache entries.
     *
     * Beware of using this method when
     * using shared memory cache systems, as it will wipe every
     * entry within the system for all clients.
     *
     *
     * @return  boolean
     */
    abstract public function delete_all();

    /**
     * Replaces troublesome characters with underscores.
     *
     * @param   string  $id  id of cache to sanitize
     * @return  string
     */
    protected function fixID($id)
    {
        // Change slashes and spaces to underscores
        return str_replace(array('/', '\\', ' '), '_', $id);
    }
}