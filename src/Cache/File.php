<?php
namespace Garden\Cache;
/**
* 
*/
class File extends \Garden\Cache
{
    public $lifetime;
    public $cacheDir;

    public $packFunction = 'serialize';
    public $unpackFunction = 'unserialize';

    protected $dirty;

    function __construct($config)
    {
        $this->lifetime       = val('defaultLifetime', $config, parent::DEFAULT_LIFETIME);
        $this->packFunction   = val('packFunction', $config, $this->packFunction);
        $this->unpackFunction = val('unpackFunction', $config, $this->unpackFunction);

        $cacheDir = val('cacheDir', $config);

        $this->cacheDir = $cacheDir ? realpath(PATH_ROOT.'/'.$cacheDir) : PATH_CACHE;

        $this->dirty = \Garden\Gdn::dirtyCache();
    }

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

    protected function getFileName($id) {
        $id = $this->fixID($id);
        $salt = substr(md5($id), 0, 10);

        return $id.'-'.$salt.'.cache';
    }

    /**
     * Retrieve a cached value entry by id.
     *
     * @param   string  $id       id of cache to entry
     * @param   string  $default  default value to return if cache miss
     * @return  mixed
     */
    public function get($id, $default = false)
    {
        $fileName = $this->getFileName($id);
        $data = false;

        if(!self::$clear && !$data = $this->dirty->get($fileName)) {

            $file = $this->cacheDir."/".$fileName;

            if(!is_file($file)) {
                return $default;
            }

            $unpackFunction = $this->unpackFunction;

            $result = file_get_contents($file);
            $result = $unpackFunction($result);
            $expire = val('expire', $result, 0);
            $data   = val('data', $result, false);

            if($expire !== false && mktime() > $expire) {
                $this->delete($id);
                return $default;
            }

            //save to temporary cache
            $this->dirty->add($fileName, $data);
        }

        return $data ?: $default;
    }

    public function exists($id)
    {
        $file = $this->cacheDir."/".$this->getFileName($id);

        return (!self::$clear && is_file($file));
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
        $packFunction = $this->packFunction;
        $cacheData = $packFunction($cacheData);

        if(!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }

        $fileName = $this->getFileName($id);
        $cachePath = $this->cacheDir."/".$fileName;

        $result = file_put_contents($cachePath, $cacheData);
        chmod($cachePath, 0664);

        $this->dirty->set($fileName, $data);

        return (bool)$result;
    }

    public function add($id, $data, $lifetime = null)
    {
        if(!$this->exists($id)) {
            return $this->set($id, $data, $lifetime);
        } else {
            return false;
        }
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
    public function deleteAll()
    {
        $dir = scandir($this->cacheDir);
        $regexp = '/([\w-_]+).cache/';
        foreach ($dir as $filename) {
            if(preg_match($regexp, $filename)) {
                $file = $this->cacheDir.'/'.$filename;
                if(!is_dir($file)) {
                    unlink($file);
                }
            }

        }
    }
}