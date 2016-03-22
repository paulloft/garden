<?php
namespace Garden\Cache;

class Rough extends \Garden\Cache
{
    protected $dirty;

    function __construct($config)
    {
        $this->dirty = \Garden\Gdn::dirtyCache();
    }

    protected function getFile($id)
    {
        return "/$id.json";
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
        $file = $this->getFile($id);

        if(!$data = $this->dirty->get($file)) {

            $filePath = PATH_CACHE.$file;

            if(!is_file($filePath)) {
                return $default;
            }

            $result = file_get_contents($filePath);
            $data = json_encode($result);

            //save to temporary cache
            $this->dirty->add($file, $data);
        }

        return $data ?: $default;
    }

        /**
     * Set a value to cache with id and lifetime
     *
     * @param   string   $id        id of cache entry
     * @param   string   $data      data to set to cache
     * @return  boolean
     */
    public function set($id, $data)
    {
        $cacheData = json_decode($data);

        if(!is_dir(PATH_CACHE)) {
            mkdir(PATH_CACHE, 0777, true);
        }

        $file = $this->getFile($id);

        $filePath = PATH_CACHE.$file;

        $result = file_put_contents($filePath, $cacheData);
        chmod($filePath, 0664);

        $this->dirty->add($file, $data);

        return (bool)$result;
    }
}