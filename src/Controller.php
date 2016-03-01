<?php
namespace Garden;
use \Garden\Exception as Exception;

class Controller extends Plugin {

    // data storage
    protected $data;
    protected $view;
    protected $controllerName;
    protected $addonFolder;

    private $smarty;

    public function __construct()
    {
        
    }

    public function setData($key, $value = null)
    {
        if(is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $value;
        }
    }

    public function data($key, $default = false)
    {
        return val($key, $this->data, $default);
    }

    public function title($title)
    {
        $this->setData('title', $title);
    }

    public function setView($view = false, $controllerName = false, $addonFolder = false)
    {
        $this->view = $view;
        $this->controllerName = $controllerName;
        $this->addonFolder = $addonFolder;
    }

    public function render($view = false, $controllerName = false, $addonFolder = false)
    {
        Event::fire('beforeRender');
        
        $view = $this->fetchView($view, $controllerName, $addonFolder);
        echo $view;

        Event::fire('afterRender');
    }

    public function fetchView($view = false, $controllerName = false, $addonFolder = false)
    {
        $viewPath = realpath(PATH_ROOT.'/'.$this->getViewPath($view, $controllerName, $addonFolder));

        if(!is_file($viewPath)) {
            throw new Exception\NotFoundException();
        }

        if(str_ends($viewPath, '.tpl')) {
            $smarty = $this->smarty();
            $smarty->assign($this->data);
            $view = $smarty->fetch($viewPath);
        } else {
            $view = \getInclude($viewPath, $this->data);
        }

        return $view;
    }

    public function getViewPath($view = false, $controllerName = false, $addonFolder = false)
    {
        $view = $view ?: $this->view;
        $addonFolder = $addonFolder ?: $this->addonFolder;
        $controllerName = $controllerName ?: $this->controllerName;

        $addonFolder = $addonFolder ?: $this->controllerInfo('folder');
        $controllerName = $controllerName ?: $this->controllerInfo('controller');

        if(str_ends($controllerName, 'controller')) {
            $controllerName = substr($controllerName, 0, -10);
        }

        $pathinfo = pathinfo($view);
        $filename = val('filename', $pathinfo, 'index');
        $ext = val('extension', $pathinfo, 'tpl');

        return $addonFolder.'/views/'.strtolower($controllerName).'/'.$filename.'.'.$ext;
    }

    protected function controllerInfo($key = false, $default = false)
    {
        $className = get_called_class();

        if(!$result = Gdn::dirtyCache()->get($className)) {
            $space = explode('\\', $className);

            if(count($space) < 3) {
                $result = false;
            } else {
                $result = array(
                    'addon' => $space[1],
                    'folder' => strtolower($space[0]).'/'.$space[1],
                    'controller' => array_pop($space)
                );
            }
            Gdn::dirtyCache()->set($className, $result);
        }

        return $key ? val($key, $result, $default) : $result;
    }

    public function smarty()
    {
        if(is_null($this->smarty)) {
            if(!class_exists('\Smarty')) {
                throw new Exception\UserException('Smarty class does not exists');
            }
            $this->smarty = new \Smarty();
            
            $config = c('smarty');
            $this->smarty->caching     = val('caching', $config, false);
            $this->smarty->compile_dir = val('compile_dir', $config, PATH_CACHE.'/smarty/');
            $this->smarty->cache_dir   = val('cache_dir', $config, PATH_CACHE.'/smarty/');
            $this->smarty->plugins_dir = val('plugins_dir', $config, false);
        }

        return $this->smarty;
    }

}
