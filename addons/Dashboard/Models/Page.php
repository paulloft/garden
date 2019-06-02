<?php

namespace Addons\Dashboard\Models;

use Garden\Exception;
use Garden\Request;
use Garden\Response;

abstract class Page {

    protected $checkAuth = true;
    /**
     * Check the authentication before initializing the page
     *
     * @param bool $checkAuth
     */
    public function initialize()
    {
        $this->checkAuth AND $this->checkAuth();
    }

    /**
     * Chech the permision
     *
     * @param $permission
     */
    protected function permission($permission)
    {
        if (!Permission::instance()->check($permission)) {
            throw new Exception\Forbidden;
        }
    }

    /**
     * Redirect to the login page if user is not logged in.
     */
    protected function checkAuth()
    {
        if (!Auth::instance()->logined()) {
            $uri = Request::current()->makeUrl();
            Response::current()->redirect("/entry/login?target=$uri");
        }
    }
}