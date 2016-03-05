<?php
namespace Garden;

class Template extends Controller {

    // template file
    protected $template = 'template';
    protected $addonName;

    protected $_js  = array();
    protected $_css = array();

    protected $meta = array();

    // default js && css folders
    public $jsFolder  = '/js';
    public $cssFolder = '/css';
    
    public function __construct()
    {
        parent::__construct();
    }

    public function addJs($src, $folder = false)
    {
        $src = ($folder === false ? $this->jsFolder : $folder).'/'.$src;
        $this->_js[md5($src)] = $src;
    }

    public function addCss($src, $folder = false)
    {
        $src = ($folder === false ? $this->cssFolder : $folder).'/'.$src;
        $this->_css[md5($src)] = $src;
    }

    public function meta($name, $content, $http_equiv = false)
    {
        $this->meta[$name] = array($content, $http_equiv);
    }

    public function template($template = false, $addonName = false)
    {
        if($template) {
            $this->template  = $template;
            $this->addonName = $addonName;
        }

        return $this->template;
    }

    public function render($view = false, $controllerName = false, $addonFolder = false)
    {
        Event::fire('beforeRender');

        $view = $view ?: $this->callerMethod();
        $view = $this->fetchView($view, $controllerName, $addonFolder);

        $this->smarty()->assign('gdn', array(
            'content'  => $view,
            'meta'     => $this->meta,
            'js'       => $this->_js,
            'css'      => $this->_css,
        ));
        $this->smarty()->assign('sitename', c('main.sitename'));
        $template = $this->fetchView($this->template, '/', $this->addonName);

        echo $template;

        Event::fire('afterRender');
    }
}