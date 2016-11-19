<?php
namespace Addons\Dashboard\Models;
use \Garden\Gdn;

/**
 * Basic auth model
 * @uses Auth::instance() 
 */

class Auth extends \Garden\Plugin
{
    /**
     * @var object $user User data object
     */
    public $user;

    protected $userModel;
    protected $session;
    
    public function __construct()
    {
        $this->userModel = Users::instance();
        $this->session = Gdn::session();
    }

    /**
     * Check user logined 
     *
     * @return boolean
     */
    public function logined()
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
        return (bool)($this->user && $this->user['admin']);
    }

    /**
     * Returns the user according to his login and password 
     *
     * @param string $username 
     * @param string $password 
     * @return object 
     */
    public function login($username, $password)
    {
        if (!$user = $this->userModel->getLogin($username))
            return false;

        if (!$user['active'])
            return false;

        $this->user = $user;

        if (!$this->checkPassword($password))
            return false;

        return $this->user;
    }

    /**
     * Authorizes the user who came for the data
     *
     * @param object  $user     User data object
     * @param boolean $remember if FALSE user will be authorized only for the current session 
     * @return object 
     */
    public function completeLogin($user, $remember = false)
    {
        if ($userID = val('id', $user)) {
            $this->session->create($userID, $remember);
            $this->updateVisit($userID);

            \Garden\Event::fire('after_login');
        }  
    }

    /**
     * Check and autologin user, if cookie is exist and valid
     *
     * @return boolean|object user data object
     */
    public function autoLogin()
    {
        $userID = $this->session->get();
        if (!$userID) 
            return false;

        $user = $this->userModel->getID($userID);

        if ($user && $user['active']) {
            $this->session->start($userID);

            $this->user = $user;
            $this->updateVisit($userID);

            return $this->user;
        } else {
            $this->session->end($userID);
            return false;
        }
    }

    /**
     * Forced login by user ID 
     *
     * @param integer $userID
     * @return boolean
     */
    public function forceLogin($userID)
    {
        if ($user = $this->userModel->getID($userID)) {
            $this->logout();
            $this->completeLogin($user);
            return true;
        } else {
            return false;
        }
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