<?php
namespace Garden\Cache;
use \Garden\Exception as Exception;
/**
* 
*/
class Memcache extends \Garden\Cache
{
    protected $lifetime;

    protected $host = 'localhost';
    protected $port = 11211;
    protected $persistent = false;
    protected $prefix = 'gdn_';

    protected $salt;
    protected $dirty;

    public $cache;

    function __construct($config = false){
        $this->lifetime = val('defaultLifetime', $config, parent::DEFAULT_LIFETIME);
        $this->persistent = val('persistent', $config, $this->persistent);

        $this->host = val('host', $config, $this->host);
        $this->port = val('port', $config, $this->port);
        $this->prefix = val('keyPrefix', $config, $this->prefix);

        $this->salt = c('main.hashsalt', 'gdn');

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
        return $this->prefix.md5($id.$this->salt);
    }

    public function get($id, $default = false)
    {
        $id = $this->fixID($id);
        $result = false;
        
        if(!self::$clear && !$result = $this->dirty->get($id)) {
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
        $this->dirty->set($id, $data);

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