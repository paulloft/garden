<?php
namespace Garden\Cache\Driver;
use \Garden\Exception\UserException;
/**
* 
*/
class File extends \Garden\Cache\Cache
{
    public $lifetime;
    public $cacheDir;

    function __construct($config)
    {
        $this->lifetime = val('defaultLifetime', $config, parent::DEFAULT_LIFETIME);
        $cacheDir = val('cacheDir', $config);
        $this->cacheDir = $cacheDir ? realpath(PATH_ROOT.'/'.$cacheDir) : PATH_CACHE;        
    }

    protected function getFileName($id) {
        $id = $this->fixID($id);
        $salt = substr(md5($id), 0, 10);

        return $id.'-'.$salt.'.ser';
    }

    /**
     * Retrieve a cached value entry by id.
     *
     * @param   string  $id       id of cache to entry
     * @param   string  $default  default value to return if cache miss
     * @return  mixed
     * @throws  Cache_Exception
     */
    public function get($id, $default = null)
    {
        $file = $this->cacheDir."/".$this->getFileName($id);
        if(!file_exists($file)) {
            return $default;
        }

        $result = file_get_contents($file);
        $result = unserialize($result);
        $expire = val('expire', $result, 0);
        $data = val('data', $result, null);

        if($expire !== false && mktime() > $expire) {
            $this->delete($id);
            return $default;
        }

        return $data;
    }

    /**
     * Set a value to cache with id and lifetime
     *
     * @param   string   $id        id of cache entry
     * @param   string   $data      data to set to cache
     * @param   integer  $lifetime  lifetime in seconds
     * @return  boolean
     */
    public function set($id, $data, $lifetime = null)
    {
        if(is_null($lifetime)) $lifetime = $this->lifetime;

        $cacheData = array(
            'expire' => $lifetime === false ? false : (mktime() + intval($lifetime)),
            'data' => $data
        );

        $cacheData = serialize($cacheData);

        if(!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        $cachePath = $this->cacheDir."/".$this->getFileName($id);

        file_put_contents($cachePath, $cacheData);
        chmod($cachePath, 0664);
    }

    /**
     * Delete a cache entry based on id
     *
     * @param   string  $id  id to remove from cache
     * @return  boolean
     */
    public function delete($id)
    {
        unlink($this->cacheDir."/".$this->getFileName($id));
    }

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
    public function delete_all()
    {

    }
}