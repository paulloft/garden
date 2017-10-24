<?php

/**
 * Class HeaderModule
 */
class HeaderModule {

    use \Garden\Traits\Singleton;

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
    public function render()
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