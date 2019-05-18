<?php

namespace Addons\Dashboard\Controllers;

use Addons\Dashboard\Models as Model;
use Garden\Config;
use Garden\Request;
use Garden\Gdn;
use Garden\Response;
use Garden\Translate;

class Entry extends Base {
    public function __construct()
    {
        parent::__construct(false);
    }

    public function pageInit()
    {
        parent::pageInit();
        $this->addJs('entry.js');
        $this->addCss('entry.css');
        $this->template('empty', 'Dashboard');
    }

    public function index()
    {
        Response::current()->redirect('/entry/login');
    }

    public function login()
    {
        $this->pageInit();

        $this->title('Authorization');

        $auth = Model\Auth::instance();
        $request = Request::current();

        $target = $request->getQuery('target');

        $error = false;

        if ($request->isPost()) {
            $username = $request->getInput('username', false);
            $password = $request->getInput('password', false);
            $remember = $request->getInput('remember', false);

            $user = $auth->login($username, $password);

            if (val('active', $user)) {
                $auth->completeLogin($user, $remember);
                Response::current()->redirect($target ?: '/');
            } elseif ($user) {
                $error = Translate::get('Account has been deactivated');
            } else {
                $error = Translate::get('Login failed');
            }
        }

        $target = htmlspecialchars($target ? '?target=' . $target : null);

        $this->setData('error', $error);
        $this->setData('target', $target);
        $this->setData('sitename', Config::get('main.sitename'));
        $this->render();
    }

    public function logout()
    {
        $target = Request::current()->getQuery('target');
        $redirect = ($target ? '?target=' . $target : '/');

        $auth = Model\Auth::instance();
        $auth->logout();

        Response::current()->redirect($redirect);
    }
}