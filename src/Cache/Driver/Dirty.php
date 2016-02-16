<?php
namespace Garden\Cache\Driver;
use \Garden\Exception\UserException;
/**
* 
*/
class Dirty extends \Garden\Cache\Cache
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
}