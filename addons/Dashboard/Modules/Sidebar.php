<?php
/**
 * Class SidebarModule
 * Left menu module
 */

namespace Addons\Dashboard\Modules;

use Garden\Helpers\Arr;
use Addons\Dashboard\Interfaces\Module;
use Garden\Request;
use Garden\Traits\Singleton;

class Sidebar implements Module {

    use Singleton;

    protected $currentUrl;
    protected $menu = [];

    private $sort = 1000;
    private $open = false;

    public function __construct()
    {
        $this->currentUrl = trim(Request::current()->getPath(), '/');
    }

    /**
     * Set the url to select the active menu item
     *
     * @param string $uri
     * @return string
     */
    public function setCurrentUrl(string $uri): string
    {
        $this->currentUrl = trim($uri, '/');

        return $this->currentUrl;
    }

    /**
     * Returns current url from sidebar
     *
     * @return string
     */
    public function getCurrentUrl(): string
    {
        return $this->currentUrl;
    }

    /**
     * Add menu group
     *
     * @param string $group group name
     * @param string $name
     * @param string $url
     * @param int $sort
     * @param string $permission
     * @param array $attributes html attributes
     * @return self
     */
    public function addGroup(string $group, string $name, string $url = '', int $sort = 0, string $permission = '', array $attributes = []): self
    {
        if ($permission && !\checkPermission($permission)) {
            return $this;
        }

        $this->menu[$group] = [
            'name' => $name,
            'url' => $url,
            'sort' => $sort ?: $this->sort++,
            'items' => Arr::path($this->menu, "$group.items", []),
            'attributes' => $attributes
        ];

        return $this;
    }

    /**
     * Add menu group link
     *
     * @param string $group group name
     * @param string $name
     * @param string $url
     * @param int $sort
     * @param string $permission
     * @param array $attributes html attributes
     * @return self
     */
    public function addItem(string $group, string $name, string $url, int $sort = 0, string $permission = '', array $attributes = []): self
    {
        if ($permission && !\checkPermission($permission)) {
            return $this;
        }

        $this->menu[$group]['items'][] = [
            'name' => $name,
            'url' => $url,
            'sort' => $sort ?: $this->sort++,
            'attributes' => $attributes
        ];

        return $this;
    }

    /**
     * Rendering function
     *
     * @return string
     */
    public function render(array $params = []): string
    {
        $html = '<ul class="nav-main">';
        $html .= $this->generateItems($this->menu, true);
        $html .= '</ul>';

        return $html;
    }


    /**
     * @param array $array
     * @param bool $groups
     * @return string
     */
    private function generateItems(array $array, bool $groups = false): string
    {
        uasort($array, [$this, 'cmp']);
        $html = '';

        foreach ($array as $group => $option) {
            $name = val('name', $option);

            if ($name === false) {
                continue;
            }

            $url = $option['url'] ?: '#';
            $items = $option['items'];
            $attributes = $option['attributes'];
            $icon = val('icon', $attributes);
            $class = val('class', $attributes, null);
            $icon = $icon ? '<i class="' . $icon . '"></i>' : null;

            unset($attributes['icon'], $attributes['class']);

            if ($groups) {
                $this->open = false;
            }

            if (trim($url, '/') === $this->currentUrl) {
                $class .= ' active';
                if (!$groups) {
                    $this->open = true;
                }
            }

            $htmlItems = false;
            if ($items) {
                $class .= ' nav-submenu';
                $attributes['data-toggle'] = 'nav-submenu';
                $htmlItems = $this->generateItems($items);
            } elseif ($url === '#') {
                continue;
            }

            if ($class) {
                $class = ' class="' . trim($class) . '"';
            }

            if ($attributes) {
                $attributes = Arr::implodeAssoc('" ', '="', $attributes) . '"';
            } else {
                $attributes = null;
            }


            $html .= '<li' . ($this->open ? ' class="open"' : null) . ($groups ? ' data-group="' . $group . '"' : '') . '>';
            if ($groups) {
                $html .= '<a href="' . $url . '"' . $class . $attributes . '>' . $icon . '<span class="text-wrapper sidebar-mini-hide">' . $option['name'] . '</span></a>';
            } else {
                $html .= '<a href="' . $url . '"' . $class . $attributes . '>' . $icon . $option['name'] . '</a>';
            }

            if ($items) {
                $html .= '<ul>' . $htmlItems . '</ul>';
            }
            $html .= '</li>';
        }

        return $html;
    }

    protected function cmp(array $a, array $b): int
    {
        return $a['sort'] <=> $b['sort'];
    }
}