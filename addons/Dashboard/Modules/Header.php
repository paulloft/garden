<?php

namespace Addons\Dashboard\Modules;

use Garden\Helpers\Arr;
use Garden\Interfaces\Module;
use Garden\Traits\Singleton;

class Header implements Module {

    use Singleton;

    protected $buttons = [];

    /**
     * Add link to header panel
     * @param $name
     * @param $href
     * @param string $type bootstrap class
     * @param bool $permission
     * @param array $attributes html attributes
     * @return bool
     */
    public function addLink($name, $href, $type = 'default', $permission = false, $attributes = array())
    {
        if ($permission && !\checkPermission($permission)) {
            return false;
        }

        if ($href) {
            $attributes['href'] = $href;
        }
        $class = val('class', $attributes, null);
        $icon = val('icon', $attributes);
        $class = trim('btn btn-' . $type . ' ' . $class);
        $attributes['class'] = $class;

        unset($attributes['icon']);

        $this->buttons[] = [
            'type' => $href ? 'a' : 'button',
            'name' => $name,
            'icon' => $icon,
            'attributes' => Arr::implodeAssoc('" ', '="', $attributes) . '"'
        ];

        return true;
    }

    /**
     * Add button to header panel
     * @param $name
     * @param string $type bootstrap class
     * @param bool $permission
     * @param array $attributes html attributes
     * @return bool
     */
    public function addButton($name, $type = 'default', $permission = false, $attributes = [])
    {
        return $this->addLink($name, false, $type, $permission, $attributes);
    }

    /**
     * Rendering functtion
     * @throws \Garden\Exception\NotFound
     * @return string
     */
    public function render(array $params = [])
    {
        $auth = \Garden\Gdn::auth();
        $controller = new \Garden\Controller;

        $username = val('name', $auth->user, '');
        $shortName = mb_strlen($username) > 12 ? mb_substr($username, 0, 12) . '...' : $username;

        $controller->setData('buttons', $this->buttons);
        $controller->setData('user', $auth->user);
        $controller->setData('shortName', $shortName);

        return $controller->fetchView('header', 'modules', 'dashboard');
    }
}