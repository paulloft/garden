<?php

/**
 * Class HeaderModule
 */
class HeaderModule extends \Garden\Controller {
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
            'attributes' => implode_assoc('" ', '="', $attributes) . '"'
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
    public function toString()
    {
        $auth = \Garden\Gdn::auth();
        $username = val('name', $auth->user, '');

        $shortName = mb_strlen($username) > 12 ? mb_substr($username, 0, 12) . '...' : $username;

        $this->setData('buttons', $this->buttons);
        $this->setData('user', $auth->user);
        $this->setData('shortName', $shortName);

        return $this->fetchView('header', 'modules', 'dashboard');
    }
}