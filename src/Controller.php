<?php
namespace Garden;
use \Garden\Exception as Exception;

class Controller extends Plugin {

    /**
     * @var Form
     */
    public $form;

    // data storage
    protected $data;
    protected $view;
    protected $controllerName;
    protected $addonFolder = 'addons';
    protected $addonName;

    protected $templateBaseDir;

    // default view extention
    protected $viewExt = 'tpl';

    private $smarty;

    public function __construct()
    {
        $this->addonName = $this->controllerInfo('addon');
    }

    /**
     * Automatically executed before the controller action. Can be used to set
     * class properties, do authorization checks, and execute other custom code.
     *
     */
    public function initialize(){}

    /**
     * Assign template data by key
     * @param string $key
     * @param mixed $value
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * Returns assigned data by key
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public function data($key, $default = false)
    {
        return val($key, $this->data, $default);
    }

    /**
     * Set page title
     * @param string $title
     */
    public function title($title)
    {
        $this->setData('title', t($title));
    }

    /**
     * Assigns the specified view
     * @param string $view name of view file
     * @param string $controllerName controller name
     * @param string $addonName addon name
     */
    public function setView($view = false, $controllerName = false, $addonName = false)
    {
        $this->view = $view;
        $this->controllerName = $controllerName;
        $this->addonName = $addonName;
    }

    /**
     * Render template
     * @param string $view name of view file
     * @param string $controllerName controller name
     * @param string $addonName addon name
     */
    public function render($view = false, $controllerName = false, $addonName = false)
    {
        Event::fire('beforeRender');

        $view = $view ?: $this->callerMethod();
        $view = $this->fetchView($view, $controllerName, $addonName);
        echo $view;

        Event::fire('afterRender');
    }

    /**
     * Returns generated html view content
     * @param string $view name of view file
     * @param string $controllerName controller name
     * @param string $addonName addon name
     * @return string html
     * @throws Exception\NotFound
     */
    public function fetchView($view, $controllerName = false, $addonName = false)
    {
        $viewPath = $this->getViewPath($view, $controllerName, $addonName);
        $realPath = realpath(PATH_ROOT.'/'.$viewPath);

        if (!is_file($realPath)) {
            throw new Exception\NotFound('Page', 'View template "'.$view.'" not found in '.$viewPath);
        }

        if (str_ends($realPath, '.'.$this->viewExt)) {
            $smarty = $this->smarty();
            $smarty->setTemplateDir(PATH_ROOT.'/'.$this->templateBaseDir);
            $smarty->assign($this->data);
            $view = $smarty->fetch($realPath);
        } else {
            $view = \getInclude($realPath, $this->data);
        }

        return $view;
    }

    /**
     * Returns view absolute file path
     * @param string $view
     * @param string $controllerName
     * @param string $addonName
     * @return string
     */
    public function getViewPath($view, $controllerName = false, $addonName = false)
    {
        $addonName = ucfirst($addonName ?: $this->addonName);
        $controllerName = $controllerName ?: $this->controllerName;

        $addonFolder = $addonName ? $this->addonFolder.'/'.$addonName : $this->controllerInfo('folder');
        $controllerName = $controllerName ?: $this->controllerInfo('controller');

        if (str_ends($controllerName, 'controller')) {
            $controllerName = substr($controllerName, 0, -10);
        }

        $pathinfo = pathinfo($view);
        $filename = val('filename', $pathinfo, 'index');
        $ext = val('extension', $pathinfo, $this->viewExt);

        $this->templateBaseDir = $addonFolder.'/views/'.strtolower($controllerName).'/';

        return $this->templateBaseDir.$filename.'.'.$ext;
    }

    /**
     * Return lazyload Smarty object
     * @return \Smarty
     * @throws Exception\Client
     */
    public function smarty()
    {
        if (is_null($this->smarty)) {
            if (!class_exists('\Smarty')) {
                throw new Exception\Client('Smarty class does not exists');
            }
            $this->smarty = new \Smarty();

            $config = c('smarty');
            $this->smarty->caching = val('caching', $config, false);
            $this->smarty
                ->setCompileDir( val('compile_dir', $config, PATH_CACHE.'/smarty/') )
                ->setCacheDir( val('cache_dir', $config, PATH_CACHE.'/smarty/') )
                ->setPluginsDir( val('plugins_dir', $config, false) );

            if (NOCACHE) {
                $this->smarty->clearAllCache();
            }
        }

        return $this->smarty;
    }

    /**
     * Return current render type
     * @return string
     */
    public function renderType()
    {
        return Request::current()->renderType();
    }

    /**
     * Return current addon name
     * @return string
     */
    public function getAddonName()
    {
        return $this->addonName;
    }

    /**
     * @param bool $tablename
     * @return Form
     */
    public function initForm($model = false, $data = false)
    {
        $tablename = is_string($model) ? $model : false;
        $this->form = new Form($tablename);

        if ($model) {
            $this->form->setModel($model, $data);
        }

        $this->setData('gdn_form', $this->form);

        return $this->form;
    }

    protected function callerMethod()
    {
        return Request::current()->getEnv('action');
    }

    protected function controllerInfo($key = false, $default = false)
    {
        $className = get_called_class();

        if (!$result = Gdn::dirtyCache()->get($className)) {
            $space = explode('\\', $className);

            if (count($space) < 3) {
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



}
