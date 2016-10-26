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
        return "$id.json";
    }

    public function get($id, $default = false)
    {
        $file = $this->getFile($id);
        $data = false;
        
        if(!self::$clear && !$data = $this->dirty->get($file)) {

            $filePath = PATH_CACHE.'/'.$file;

            if(!is_file($filePath)) {
                return $default;
            }

            $result = file_get_contents($filePath);
            $data = json_decode($result, true);

            //save to temporary cache
            $this->dirty->add($file, $data);
        }

        return $data ?: $default;
    }

    public function set($id, $data, $lifetime = 0)
    {
        $cacheData = json_encode($data, JSON_PRETTY_PRINT);

        if(!is_dir(PATH_CACHE)) {
            mkdir(PATH_CACHE, 0777, true);
        }

        $file = $this->getFile($id);

        $filePath = PATH_CACHE.'/'.$file;

        $result = file_put_contents($filePath, $cacheData);
        chmod($filePath, 0664);

        $this->dirty->set($file, $data);

        return (bool)$result;
    }

    public function deleteAll()
    {
        $dir = scandir(PATH_CACHE);
        $regexp = '/'.$this->getFile('([\w-_]+)').'/';
        foreach ($dir as $filename) {
            // echo $filename.'|';
            if(preg_match($regexp, $filename)) {
                $file = PATH_CACHE.'/'.$filename;
                if(!is_dir($file)) {
                    unlink($file);
                }
            }

        }
    }

    public function add($id, $data, $lifetime = null){}

    public function exists($id){}

    public function delete($id){}
}