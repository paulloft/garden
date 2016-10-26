<?php
namespace Garden\Cache;
use \Garden\Exception as Exception;
/**
* 
*/
class Memcached extends Memcache
{
    function __construct($config = false){
        parent::__construct($config);
    }

    protected function connect()
    {
        if(!class_exists('Memcached')) {
            throw new Exception\Custom('Memcached extention not found');
        }

        $this->cache = new \Memcached($this->persistent);
        $this->cache->addServer($this->host, $this->port);
    }

    public function set($id, $data, $lifetime = null)
    {
        if(is_null($lifetime)) $lifetime = $this->lifetime;
        $id = $this->fixID($id);
        $this->dirty->set($id, $data);

        return $this->cache->set($id, $data, intval($lifetime));
    }

    public function add($id, $data, $lifetime = null)
    {
        if(is_null($lifetime)) $lifetime = $this->lifetime;
        $id = $this->fixID($id);

        return $this->cache->add($id, $data, intval($lifetime));
    }

    public function getMessage()
    {
        return $this->cache->getResultMessage();
    }

    function __destruct()
    {
        $this->cache->quit();
    }
}