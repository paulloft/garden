<?php
namespace Addons\Dashboard\Controllers;
use Addons\Dashboard\Models as Model;
use Garden\Request;
use Garden\Gdn;
/**
* 
*/
class Entry extends \Garden\Template
{
    // protected $template = 'empty'; 
    
    function __construct()
    {
        parent::__construct();
    }

    protected function pageInit()
    {
        $this->addJs('jquery.min.js', '//ajax.googleapis.com/ajax/libs/jquery/2.1.0');
        $this->addJs('bootstrap.min.js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js');
        $this->addJs('entry.js', '/js/Dashboard');

        $this->addCss('bootstrap.min.css', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css');
        $this->addCss('bootstrap.theme.css', '/css/Dashboard');
        $this->addCss('main.css', '/css/Dashboard');
    }

    public function index()
    {
        redirect('/entry/login/');
    }

    public function login()
    {
        $this->pageInit();

        $this->title('Authorization');

        $target = Request::current()->getQuery('target');
        $target = ($target ? '?target='.$target : null);

        $this->setData('target', $target);
        $this->render();
    }

    


}