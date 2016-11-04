<?php
namespace Addons\Dashboard\Controllers;
use Addons\Dashboard\Models as Model;
use Garden\Exception as Exception;
use Garden\Gdn;
/**
* 
*/
class Base extends \Garden\Template
{
    protected $template = 'dashboard';
    protected $needAuth = true;

    protected $auth;
    
    function __construct($needAuth = null)
    {
        parent::__construct();
        if (!is_null($needAuth)) {
            $this->needAuth = $needAuth;
        }
        $this->checkAuth();
    }

    public function pageInit()
    {
        if($this->renderType() == \Garden\Request::RENDER_ALL) {
            $this->addJs('//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js');
            $this->addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
            $this->addJs('dashboard.js');

            $this->addCss('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
            $this->addCss('//fonts.googleapis.com/css?family=Open+Sans:300,400,400italic,600,700');
            $this->addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
            $this->addCss('bootstrap.theme.css');
            $this->addCss('dashboard.css');

            \Garden\Event::fire('dashboardPageInit');
        }
    }

    protected function checkAuth()
    {
        if ($this->needAuth && !Model\Auth::instance()->logined()) {
            $uri = url_local();
            redirect('/entry/login?target='.$uri);
        }
    }

    public function permission($permission)
    {
        if (!Model\Permission::instance()->check($permission)) {
            throw new Exception\Forbidden;
        }
    }

    public function currentUrl($url)
    {
        \SidebarModule::instance()->current($url);
    }
}