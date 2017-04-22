<?php
namespace Addons\Dashboard\Controllers;

use Addons\Dashboard\Models as Model;
use Garden\Request;
use Garden\Gdn;

class Entry extends \Garden\Template
{
    protected $template = 'empty';

    protected function pageInit()
    {
        $this->addJs('/js/jquery.min.js');
        $this->addJs('/js/bootstrap.min.js');
        $this->addJs('entry.js');

        $this->addCss('/css/oneui.css');
        $this->addCss('/css/bootstrap.theme.css');
        $this->addCss('//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400italic,600,700%7COpen+Sans:300,400,400italic,600,700');
        $this->addCss('entry.css');
    }

    public function index()
    {
        redirect('/entry/login');
    }

    public function login()
    {
        $this->pageInit();

        $this->title('Authorization');

        $auth = Model\Auth::instance();
        $request = Request::current();

        $target = $request->getQuery('target');

        $error = false;

        Gdn::session();

        if($request->isPost()) {
            $username = $request->getInput('username', false);
            $password = $request->getInput('password', false);
            $remember = $request->getInput('remember', false);
            
            if($user = $auth->login($username, $password)) {
                if($user['active']) {
                    $auth->completeLogin($user, $remember);
                    redirect($target ?: '/');
                } else {
                    $error = t('Account has been deactivated');
                }
            } else {
                $error = t('Login failed');
            }
        }

        $target = htmlspecialchars($target ? '?target='.$target : null);

        $this->setData('error', $error);
        $this->setData('target', $target);
        $this->render();
    }

    public function logout()
    {
        $target = Gdn::request()->getQuery('target');
        $redirect = ($target ? '?target='.$target : '/');

        $auth = Model\Auth::instance();
        $auth->logout();

        redirect($redirect);
    }

    


}