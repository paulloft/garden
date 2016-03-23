<?php
namespace Garden;

class Template extends Controller {

    // default js && css folders
    public $jsFolder  = 'js';
    public $cssFolder = 'css';

    // template file
    protected $template = 'template';
    protected $addonName;

    protected $_js  = array();
    protected $_css = array();
    protected $meta = array();
    protected $alone = array();
    
    public function __construct()
    {
        parent::__construct();
    }

    public function addJs($src, $addonName = false, $alone = false)
    {
        $addon = $addonName ?: $this->addonName;
        $src = $this->isLocalSrc($src) ? $addon.'/'.$this->jsFolder.'/'.$src : $src;

        $hash = hash('md4', $src);
        if($alone) {
            $this->alone['js'][$hash] = $src;
        } else {
            $this->_js[$hash] = $src;
        }

        if($nocached) {
            $this->nocached[$hash] = true;
        }
    }

    public function addCss($src, $addonName = false, $alone = false)
    {
        $addon = $addonName ?: $this->addonName;
        $src =  $this->isLocalSrc($src) ? $addon.'/'.$this->cssFolder.'/'.$src : $src;

        $hash = hash('md4', $src);

        if($alone) {
            $this->alone['css'][$hash] = $src;
        } else {
            $this->_css[$hash] = $src;
        }

        if($nocached) {
            $this->nocached[$hash] = true;
        }
    }

    public function meta($name, $content, $http_equiv = false)
    {
        $this->meta[$name] = array($content, $http_equiv);
    }

    public function template($template = false, $addonName = false)
    {
        if($template) $this->template   = $template;
        if($addonName) $this->addonName = $addonName;

        return $this->template;
    }

    public function render($view = false, $controllerName = false, $addonFolder = false)
    {
        Event::fire('beforeRender');

        $view = $view ?: $this->callerMethod();
        $view = $this->fetchView($view, $controllerName, $addonFolder);

        $css = $this->compress('css');
        $js = $this->compress('js');

        $this->smarty()->assign('gdn', array(
            'content'  => $view,
            'meta'     => $this->meta,
            'js'       => $js,
            'css'      => $css,
        ));
        $this->smarty()->assign('sitename', c('main.sitename'));
        $template = $this->fetchView($this->template, '/', $this->addonName);

        echo $template;

        Event::fire('afterRender');
    }

    protected function isLocalSrc($url)
    {
        return !preg_match('#^(http|\/\/)#', $url);
    }

    protected function compress($type)
    {
        $data = $this->{"_$type"};
        $keys = array_keys($data);
        $hash = md5(implode($keys));

        $array = array();

        $cacheDir = c('main.mediaCacheDir', 'cache');
        $path = "/$cacheDir/$type/";
        $fileName = "$hash.$type";
        $file = PATH_PUBLIC.$path.$fileName;

        mkdir(PATH_PUBLIC.$path, 0777, true);

        foreach ($this->alone[$type] as $key=>$src) {
            $aloneName = "$key.$type";
            $aloneFile = PATH_PUBLIC.$path.$aloneName;
            if(!file_exists($aloneFile)) {
                $result = $this->getContent($src);
                file_put_contents($aloneFile, $result);
            }
            $array[] = $path.$aloneName;
        }

        $result = '';
        if(!file_exists($file)) {
            foreach ($data as $key=>$src) {
                $result .= $this->getContent($src);
            }

            file_put_contents($file, $result);
        }

        $array[] = $path.$fileName;

        return $array;
    }

    protected function getContent($source)
    {
        if($this->isLocalSrc($source)) {
            $realSrc = PATH_ADDONS.'/'.$source;
        } else {
            $realSrc = str_replace('//', 'http://', $source);
        }
        $result = "\n/* $source */\n";
        $result .= file_get_contents($realSrc);

        return $result;
    }
}