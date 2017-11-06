<?php
/**
 * Class SidebarModule
 * Left menu module
 */

namespace Addons\Dashboard\Modules;
use Garden\Gdn;
use Garden\Interfaces\Module;
use Garden\Traits\Singleton;

class Sidebar implements Module {

    protected $current;
    protected $menu = [];

    private $sort = 1000;

    use Singleton;

    /**
     * Set the url to select the active menu item
     * @param bool $uri
     * @return string
     */
    public function currentUrl($uri = false)
    {
        if ($uri) {
            $this->current = trim($uri, '/');
        }

        if (!$this->current) {
            $this->current = trim(Gdn::request()->getPath(), '/');
        }

        return $this->current;
    }

    /**
     * Add menu group
     * @param string $group group name
     * @param string $name
     * @param bool $url
     * @param bool $sort
     * @param bool $permission
     * @param array $attributes html attributes
     */
    public function addGroup($group, $name, $url = false, $sort = false, $permission = false, $attributes = [])
    {
        if ($permission && !\checkPermission($permission)) {
            return false;
        }

        $this->menu[$group] = [
            'name'  => $name,
            'url'   => $url,
            'sort'  => $sort ?: $this->sort++,
            'items' => valr("$group.items", $this->menu, []),
            'attributes' => $attributes
        ];

        return true;
    }

    /**
     * Add menu group link
     * @param string $group group name
     * @param string $name
     * @param $url
     * @param bool $sort
     * @param bool $permission
     * @param array $attributes html attributes
     */
    public function addItem($group, $name, $url, $sort = false, $permission = false, $attributes = [])
    {
        if ($permission && !\checkPermission($permission)) {
            return false;
        }

        $this->menu[$group]['items'][] = [
            'name' => $name,
            'url'  => $url,
            'sort' => $sort ?: $this->sort++,
            'attributes' => $attributes
        ];

        return true;
    }

    /**
     * Rendering function
     * @return string
     */
    public function render(array $params = [])
    {
        $html = '<ul class="nav-main">';
        $html .= $this->generateItems($this->menu, true);
        $html .= '</ul>';

        return $html;
    }

    private $open = false;
    private function generateItems(array $array, $groups = false)
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

            if (trim($url, '/') == $this->currentUrl()) {
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
            } elseif ($url == '#') {
                continue;
            }

            if ($class) {
                $class = ' class="' . trim($class) . '"';
            }

            if ($attributes) {
                $attributes = implode_assoc('" ', '="', $attributes) . '"';
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

    protected function cmp(array $a, array $b)
    {
        if ($a['sort'] == $b['sort']) {
            return 0;
        }

        return ($a['sort'] < $b['sort']) ? -1 : 1;
    }
}