<?php

namespace Addons\Dashboard\Models;

use Garden\Helpers\Arr;
use \Garden\Traits\Singleton;

/**
 * Basic auth model
 * @uses Auth::instance()
 */
class Auth {
    /**
     * @var array $user User data object
     */
    public $user;

    protected $userModel;
    protected $session;

    use Singleton;

    private function __construct()
    {
        $this->userModel = Users::instance();
        $this->session = Session::instance();
    }

    /**
     * Check user logined
     *
     * @return boolean
     */
    public function logined(): bool
    {
        return $this->session->valid();
    }

    /**
     * Check user admin
     *
     * @return boolean
     */
    public function admin()
    {
        return ($this->user && $this->user['admin']);
    }

    /**
     * Returns the user according to his login and password
     *
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function login($username, $password)
    {
        if (!$user = $this->userModel->getByLogin($username)) {
            return false;
        }

        if (!$user['active']) {
            return false;
        }

        $this->user = $user;

        if (!$this->checkPassword($password)) {
            return false;
        }

        return $this->user;
    }

    /**
     * Authorizes the user who came for the data
     *
     * @param array $user User data object
     * @param boolean $remember if FALSE user will be authorized only for the current session
     * @return bool
     */
    public function completeLogin(array $user, $remember = false)
    {
        $userID = Arr::get($user, 'id');

        if ($userID) {
            $this->session->create($userID, $remember);
            $this->updateVisit($userID);

            \Garden\Event::fire('after_login');
            return true;
        }

        return false;
    }

    /**
     * Check and autologin user, if cookie is exist and valid
     *
     * @return void
     */
    public function authorize()
    {
        $userID = Session::currentUserID();
        if (!$userID) {
            return;
        }

        $user = $this->userModel->getID($userID);
        $active = $user['active'] ?? false;

        if (!$active) {
            $this->session->end($userID);
            return;
        }

        $this->user = $user;
        $this->updateVisit($userID);
    }

    /**
     * Forced login by user ID
     *
     * @param integer $userID
     * @return boolean
     */
    public function forceLogin($userID)
    {
        $user = $this->userModel->getID($userID);
        if ($user) {
            $this->logout();
            $this->completeLogin($user);
            return true;
        }

        return false;
    }

    public function logout($logoutAll = false)
    {
        $this->session->end($logoutAll);
        \Garden\Event::fire('after_logout');
    }

    /**
     * Check $password for compliance with the current password
     *
     * @param string $password
     * @return boolean
     */
    public function checkPassword($password)
    {
        return ($this->user && $this->user['password'] === $this->hash($password));
    }

    /**
     * Return hashed password
     *
     * @param string $password
     * @return string md5
     */
    public function hash($password)
    {
        return md5($password);
    }

    protected function updateVisit($userID)
    {
        if (!isset($_SESSION['visited'])) {
            $this->userModel->updateVisit($userID);
            $_SESSION['visited'] = true;
        }
    }
}