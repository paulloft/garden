<?php

namespace Addons\Dashboard\Modules;

use Addons\Dashboard\Models\Auth;
use Addons\Dashboard\Models\Permission;
use Garden\Exception\NotFound;
use Garden\Helpers\Arr;
use Addons\Dashboard\Interfaces\Module;
use Garden\Renderers\View;
use Garden\Response;
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
     * @return self
     */
    public function addLink(string $name, string $href, string $type = 'default', string $permission = '', array $attributes = []): self
    {
        if ($permission && !Permission::instance()->check($permission)) {
            return $this;
        }

        if ($href) {
            $attributes['href'] = $href;
        }

        $class = $attributes['class'] ?? null;
        $icon = $attributes['icon'] ?? null;
        $class = trim("btn btn-$type $class");
        $attributes['class'] = $class;

        unset($attributes['icon']);

        $this->buttons[] = [
            'type' => $href ? 'a' : 'button',
            'name' => $name,
            'icon' => $icon,
            'attributes' => Arr::implodeAssoc('" ', '="', $attributes) . '"'
        ];

        return $this;
    }

    /**
     * Add button to header panel
     * @param $name
     * @param string $type bootstrap class
     * @param bool $permission
     * @param array $attributes html attributes
     * @return self
     */
    public function addButton(string $name, string $type = 'default', string $permission = '', array $attributes = []): self
    {
        $this->addLink($name, false, $type, $permission, $attributes);

        return $this;
    }

    /**
     * Rendering functtion
     * @return string
     * @throws NotFound
     */
    public function render(array $params = []): string
    {
        $auth = Auth::instance();
        $view = new View('header', 'modules', 'dashboard');

        $view->setData('buttons', $this->buttons);
        $view->setData('user', $auth->user);

        return $view->fetch(Response::current());
    }
}