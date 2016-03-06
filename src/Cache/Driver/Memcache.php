<?php
namespace Garden\Cache\Driver;
use \Garden\Exception as Exception;
/**
* 
*/
class Memcache extends \Garden\Cache\Cache
{
    protected $lifetime;

    protected $host = 'localhost';
    protected $port = 11211;
    protected $persistent = false;

    protected $salt = 'gdn';
    protected $dirty;

    public $cache;

    function __construct($config = false){
        $this->lifetime = val('defaultLifetime', $config, parent::DEFAULT_LIFETIME);
        $this->persistent = val('persistent', $config, $this->persistent);

        $this->host = val('host', $config, $this->host);
        $this->port = val('port', $config, $this->port);

        $this->salt = c('main.hashsalt') ?: $this->salt;

        $this->dirty = \Garden\Gdn::dirtyCache();

        $this->connect();
    }

    protected function connect()
    {
        if(!class_exists('memcache')) {
            throw new Exception\Custom('memcache extention not found');
        }

        $this->cache = new \Memcache();
        $this->cache->addServer($this->host, $this->port, $this->persistent);
    }

    protected function fixID($id)
    {
        return md5($id.$this->salt);
    }

    public function get($id, $default = false)
    {
        $id = $this->fixID($id);
        if(!$result = $this->dirty->get($id)) {
            $result = $this->cache->get($id);
            //save to temporary cache
            $this->dirty->add($id, $result);
        }
        return $result ?: $default;
    }
    
    public function set($id, $data, $lifetime = null)
    {
        if(is_null($lifetime)) $lifetime = $this->lifetime;
        $id = $this->fixID($id);
        $this->dirty->delete($id);

        return $this->cache->set($id, $data, MEMCACHE_COMPRESSED, intval($lifetime));
    }

    public function add($id, $data, $lifetime = null)
    {
        if(is_null($lifetime)) $lifetime = $this->lifetime;
        $id = $this->fixID($id);

        return $this->cache->add($id, $data, MEMCACHE_COMPRESSED, intval($lifetime));
    }

    public function exists($id)
    {
        return (bool)$this->get($id);
    }

    public function delete($id)
    {
        $id = $this->fixID($id);
        $this->dirty->delete($id);
        $this->cache->delete($id);
    }

    public function deleteAll()
    {
        $this->cache->flush();
    }



    function __destruct()
    {
        $this->cache->close();
    }
}