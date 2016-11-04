<?php
namespace Garden;

/**
 * @author PaulLoft <info@paulloft.ru>
 * @copyright 2016 Paulloft
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package Garden
 */
class Session extends Plugin
{

    /**
     * Current user id
     * @var int
     */
    public $userID = 0;

    protected $model;

    function __construct()
    {
        $this->model = new Model('session');
        $this->model->primaryKey = 'sessionID';
        session_start();
    }

    /**
     * Check user authorization
     * @return bool
     */
    public function valid()
    {
        return $this->userID > 0;
    }

    /**
     * set id for current user
     * @param int $userID
     */
    public function start($userID)
    {
        $this->userID = $userID;
    }

    /**
     * Create new session
     * @param int $userID
     * @param bool $remember if false user session will be ended after the close window
     */
    public function create($userID, $remember = false)
    {
        $salt = c('main.hashsalt');
        $lifetime = c('session.lifetime');
        $sessionID = md5($salt.$userID.session_id());


        $data = array(
            "sessionID" => $sessionID,
            "userID" => $userID,
            "expire" => date_sql(time() + ($remember ? $lifetime : (60*60*8))),
            "lastActivity" => DB::expr('now()'),
            "userAgent" => Gdn::request()->getEnv('HTTP_USER_AGENT')
        );
//        d($data);

        // if ($remember) {
        // $data["lastActivity"] = DB::expr('now()');
            if ($this->model->getID($sessionID)) {
                $this->model->update($sessionID, $data);
            } else {
                $this->model->insert($data);
            }
        // }

        // $_SESSION[$sessionID] = (object)$data;
        $this->setCookie('sessionid', $sessionID, ($remember ? $lifetime : 0));

        $this->start($userID);
    }

    /**
     * Close session for user by userID
     * @param int $userID
     * @param bool $endAll if true all sessions will be closed
     */
    public function end($userID = false, $endAll = false)
    {
        $sessionID = $this->getCookie('sessionid');

        $this->deleteCookie('sessionid');

        $where = $endAll ? array('userID' => $userID ?: $this->userID) : array('sessionID' => $sessionID);
        $this->model->delete($where);

        if ($this->userID === $userID) {
            $this->userID = 0;
        }

        session_destroy();
    }

    public function get()
    {
        $sessionID = $this->getCookie('sessionid');
        if (!$sessionID) return false;

        // if (!$_SESSION[$sessionID]) {
            $session = $this->model->getID($sessionID);
            if (!$session) return false;

        //     $_SESSION[$sessionID] = $session;
        // } else {
        //     $session = $_SESSION[$sessionID];
        // }

        $userID = val('userID', $session);
        $expire = val('expire', $session);

        if (time() > strtotime($expire)) {
            $this->end($userID);
            return false;
        }

        return $userID;
    }

    /**
     * set cookie value
     * @param string $name
     * @param string $value
     * @param int $lifetime
     */
    public function setCookie($name, $value, $lifetime)
    {
        $name   = c('session.cookie.prefix').$name;
        $path   = c('session.cookie.path');
        $domain = c('session.cookie.domain');


        // If the domain being set is completely incompatible with the current domain then make the domain work.
        $host = Gdn::request()->getHost();
        if (!str_ends($host, trim($domain, '.'))) {
            $domain = '';
        }

        if ($lifetime < 0) {
            unset($_COOKIE[$name]);
            $expires = -1;
        } else {
            $_COOKIE[$name] = $value;
            $expires = $lifetime ? time() + $lifetime : 0;
        }
        setcookie($name, $value, $expires, $path, $domain);
    }

    /**
     * get cookie value
     * @param $name
     * @param string|bool $default if cookie is non exists return $default value
     * @param bool $usePrefix use or not system cookie prefix
     * @return string|bool
     */
    public function getCookie($name, $default = false, $usePrefix = true)
    {
        $name = $usePrefix ? c('session.cookie.prefix').$name : $name;
        return val($name, $_COOKIE, $default);
    }

    /**
     * delete cookie
     * @param string $name
     */
    public function deleteCookie($name)
    {
        $this->setCookie($name, null, -1);
    }
}