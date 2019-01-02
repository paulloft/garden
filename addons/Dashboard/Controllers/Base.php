<?php
namespace Addons\Dashboard\Controllers;

use Addons\Dashboard\Models as Model;
use Addons\Dashboard\Modules\Sidebar;
use Garden\Exception;
use Garden\Request;
use Garden\Response;

class Base extends \Garden\Template
{
    protected $needAuth;

    protected $auth;

    public function __construct($needAuth = true)
    {
        parent::__construct();
        $this->needAuth = $needAuth;
        $this->checkAuth();
        $this->template('dashboard', 'Dashboard');
    }

    public function pageInit()
    {
        if($this->renderType() === Request::RENDER_ALL) {
            $this->addCss('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
            $this->addCss('//fonts.googleapis.com/css?family=Open+Sans:300,400,400italic,600,700');
            $this->addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
            $this->addCss('bootstrap.theme.css');
            $this->addCss('dashboard.css');

            $this->addJs('//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js');
            $this->addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
            $this->addJs('dashboard.js');

            \Garden\Event::fire('dashboard_page_init');
        }
    }

    protected function checkAuth()
    {
        if ($this->needAuth && !Model\Auth::instance()->logined()) {
            $uri = Request::current()->makeUrl();
            Response::current()->redirect('/entry/login?target=' . $uri);
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
        Sidebar::instance()->currentUrl($url);
    }
}