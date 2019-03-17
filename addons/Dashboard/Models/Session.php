<?php

namespace Addons\Dashboard\Models;

use Garden\Config;
use Garden\Gdn;
use Garden\Helpers\Arr;
use Garden\Helpers\Date;
use Garden\Helpers\Text;
use Garden\Db\DB;
use Garden\Model;
use Garden\Request;

/**
 * @author PaulLoft <info@paulloft.ru>
 * @copyright 2016 Paulloft
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 */
class Session {
    const SESSION_KEY = 'sessionid';

    protected $model;

    /**
     * Current user id
     * @var int
     */
    private static $userID = 0;

    private static $_instance;

    /**
     * Init session
     */
    public static function init()
    {
        session_start();
        self::$_instance = new self;
        self::$userID = self::$_instance->getUserID();
        Request::current()->setEnv('USER_ID', self::$userID);
    }

    /**
     * Get the session instance
     * @return Session
     */
    public static function instance(): self
    {
        if (self::$_instance === null) {
            self::init();
        }

        return self::$_instance;
    }

    public static function currentUserID(): int
    {
        return self::$userID;
    }

    /**
     * Singletone session constructor.
     */
    private function __construct()
    {
        $this->model = new Model('session', 'session_id');
    }

    /**
     * Check user authorization
     * @return bool
     */
    public function valid()
    {
        return self::$userID > 0;
    }

    /**
     * set id for current user
     * @param int $userID
     */
    public function start($userID)
    {
        self::$userID = $userID;
    }

    /**
     * Create new session
     * @param int $userID
     * @param bool $remember if false user session will be ended after the close window
     */
    public function create($userID, $remember = false)
    {
        $salt = Config::get('main.hashsalt');
        $lifetime = Config::get('session.lifetime');
        $sessionID = md5($salt . $userID . session_id() . time());
        $expireDate = Date::create()
            ->addSeconds($remember ? $lifetime : 60 * 60 * 8)
            ->toSql();

        $this->model->insert([
            'session_id' => $sessionID,
            'user_id' => $userID,
            'expire' => $expireDate,
            'ip' => Gdn::request()->getIP(),
            'last_activity' => DB::expr('now()'),
            'user_agent' => Gdn::request()->getEnvKey('HTTP_USER_AGENT'),
        ]);

        self::$userID = $userID;
        self::setCookie(self::SESSION_KEY, $sessionID, ($remember ? $lifetime : 0));
    }

    /**
     * Close session for user by userID
     * @param int $userID
     * @param bool $endAll if true all sessions will be closed
     */
    public function end($userID = false, $endAll = false)
    {
        $sessionID = self::getCookie(self::SESSION_KEY);

        self::deleteCookie(self::SESSION_KEY);

        $where = $endAll ? ['user_id' => $userID ?: self::$userID] : ['session_id' => $sessionID];
        $this->model->delete($where);

        if (self::$userID === $userID) {
            self::$userID = 0;
            session_destroy();
        }
    }

    /**
     * Get session userID
     * @return int
     */
    protected function getUserID(): int
    {
        $sessionID = self::getCookie(self::SESSION_KEY);
        if (!$sessionID) {
            return 0;
        }

        $session = $this->model->getID($sessionID);
        if (!$session) {
            return 0;
        }

        $userID = Arr::get($session, 'user_id');
        $expire = Arr::get($session, 'expire');

        if (time() > strtotime($expire)) {
            $this->end($userID);
            return 0;
        }

        return $userID;
    }

    /**
     * Update last activity
     */
    public function update()
    {
        $sessionID = self::getCookie(self::SESSION_KEY);
        if (!$sessionID) {
            return;
        }

        $this->model->update($sessionID, [
            'last_activity' => DB::expr('now()'),
            'last_ip' => Gdn::request()->getIP()
        ]);
    }

    /**
     * set cookie value
     * @param string $name
     * @param string $value
     * @param int $lifetime
     */
    public static function setCookie($name, $value, $lifetime)
    {
        $name = Config::get('session.cookie.prefix') . $name;
        $path = Config::get('session.cookie.path');
        $domain = Config::get('session.cookie.domain');

        // If the domain being set is completely incompatible with the current domain then make the domain work.
        $host = Gdn::request()->getHost();
        if (!Text::strEnds($host, trim($domain, '.'))) {
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
    public static function getCookie($name, $default = false, $usePrefix = true)
    {
        $name = $usePrefix ? Config::get('session.cookie.prefix') . $name : $name;
        return Arr::get($_COOKIE, $name, $default);
    }

    /**
     * delete cookie
     * @param string $name
     */
    public static function deleteCookie($name)
    {
        self::setCookie($name, null, -1);
    }
}