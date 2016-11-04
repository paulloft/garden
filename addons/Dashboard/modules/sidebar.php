<?php
use Garden\Gdn;
/**
* 
*/
class SidebarModule extends \Garden\Plugin
{

    protected $current;
    protected $menu = array();

    private $sort = 1000;

    public function current($uri = false)
    {
        if ($uri) {
            $this->current = trim($uri, '/');
        }
        if (!$this->current) {
            $this->current = trim(Gdn::request()->getPath(), '/');
        }
        return $this->current;
    }

    public function addGroup($group, $name, $url = false, $sort = false, $permission = false, $attributes = array())
    {
        if ($permission && !\checkPermission($permission))
            return false;

        $this->menu[$group] = array(
            "name"  => $name,
            "url"   => $url,
            "sort"  => ($sort ?: $this->sort++),
            "items" => array(),
            "attributes" => $attributes
        );
    }

    public function addItem($group, $name, $url, $sort = false, $permission = false, $attributes = array())
    {
        if ($permission && !\checkPermission($permission))
            return false;

        $this->menu[$group]['items'][] = array(
            "name" => $name,
            "url"  => $url,
            "sort" => ($sort ?: $this->sort++),
            "attributes" => $attributes
        );
    }

    public function toString()
    {
        $html  = '<ul class="nav-main">';
        $html .= $this->generateItems($this->menu, true);
        $html .= '</ul>';

        return $html;
    }

    private $open = false;
    private function generateItems($array, $groups = false)
    {
        uasort($array, array($this, 'cmp'));

        foreach ($array as $group => $option) {
            $url = $option['url'] ?: '#';
            $items = $option['items'];
            $attributes = $option['attributes'];
            $icon = val('icon', $attributes);
            $class = val('class', $attributes, null);
            $icon = $icon ? '<i class="'.$icon.'"></i>' : null;

            unset($attributes['icon'], $attributes['class']);

            if ($groups) {
                $this->open = false;
            }

            if (trim($url, '/') == $this->current()) {
                $class .= ' active';
                if (!$groups) $this->open = true;
            }

            if ($items) {
                $class .= ' nav-submenu';
                $htmlItems = $this->generateItems($items);
                $attributes['data-toggle'] = 'nav-submenu';
            }

            if ($class) {
                $class = ' class="'.trim($class).'"';
            }

            if ($attributes) {
                $attributes = implode_assoc('" ', '="', $attributes).'"';
            } else {
                $attributes = null;
            }


            if ($option['url'] OR $items) {
                $html .= '<li'.($this->open ? ' class="open"' : null).($groups ? ' data-group="'.$group.'"': '').'>';
                if ($groups) {
                    $html .= '<a href="'.$url.'"'.$class.$attributes.'>'.$icon.'<span class="text-wrapper sidebar-mini-hide">'.t($option['name']).'</span></a>';
                } else {
                    $html .= '<a href="'.$url.'"'.$class.$attributes.'>'.$icon.t($option['name']).'</a>';
                }

                if ($items) {
                    $html .= '<ul>'.$htmlItems.'</ul>';
                }
                $html .= '</li>';
            }
        }

        return $html;
    }

    protected function cmp($a, $b)
    {
        if ($a['sort'] == $b['sort']) {
            return 0;
        }
        return ($a['sort'] < $b['sort']) ? -1 : 1;
    }
}