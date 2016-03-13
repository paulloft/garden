<?php
namespace Addons\Dashboard\Controllers;
use Addons\Dashboard\Models as Model;
use Garden\Request;
use Garden\Gdn;
/**
* 
*/
class Dashboard extends \Garden\Template
{
    
    function __construct()
    {
        parent::__construct();
    }

    protected function pageInit()
    {
        $this->addJs('jquery.min.js', '//ajax.googleapis.com/ajax/libs/jquery/2.1.0');
        $this->addJs('bootstrap.min.js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js');

        $this->addCss('bootstrap.min.css', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css');
        $this->addCss('bootstrap.theme.css', '/css/Dashboard');
        $this->addCss('main.css', '/css/Dashboard');
    }

    public function index()
    {
        
    }

}