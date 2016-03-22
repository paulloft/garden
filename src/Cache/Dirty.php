<?php
namespace Garden\Cache;
/**
* 
*/
class Dirty extends \Garden\Cache
{
    protected $config;
    protected $data = [];

    public function __construct($config = false){
        $this->config = $config;
    }

    public function get($id, $default = null)
    {
        return val($id, $this->data, $default);
    }
    
    public function set($id, $data, $lifetime = 3600)
    {
        $this->data[$id] = $data;
        return true;
    }

    public function add($id, $data, $lifetime = 3600)
    {
        if(!isset($this->data[$id])) {
            $this->data[$id] = $data;
            return true;
        } else {
            return false;
        }
    }

    public function exists($id)
    {
        return isset($this->data[$id]);
    }

    public function delete($id)
    {
        unset($this->data[$id]);
    }

    public function deleteAll()
    {
        $this->data = [];
    }

    public function cacheGet($key, callable $cache_cb) {
        $cache_path = PATH_CACHE."/$key.json";
        if(!$result = $this->get($key)) {
            if (file_exists($cache_path)) {
                $result = array_load($cache_path);
            } else {
                $result = $cache_cb();
                array_save($result, $cache_path);
            }
            $this->add($key, $result);
        }
        return $result;
    }
}