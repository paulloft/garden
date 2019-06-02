<?php

namespace Addons\Dashboard\Controllers;

use Addons\Dashboard\Models as Model;
use Garden\Config;
use Garden\Renderers\Template;
use Garden\Request;
use Garden\Response;
use Garden\Translate;

class Entry {

    public function index()
    {
        Response::current()->redirect('/entry/login');
    }

    /**
     * Login page
     *
     * @return Template
     */
    public function login(): Template
    {
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

        $target = htmlspecialchars($target ? "?target=$target"  : null);

        return  Model\Template::getEmpty()
            ->addJs('entry.js')
            ->addCss('entry.css')
            ->setTitle('Authorization')
            ->setData('error', $error)
            ->setData('target', $target)
            ->setData('sitename', Config::get('main.sitename'));
    }


    /**
     * Logout page
     */
    public function logout()
    {
        $target = Request::current()->getQuery('target');
        $redirect = ($target ? "?target=$target" : '/');

        $auth = Model\Auth::instance();
        $auth->logout();

        Response::current()->redirect($redirect);
    }
}