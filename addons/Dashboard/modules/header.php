<?php

/**
* 
*/
class HeaderModule extends \Garden\Controller
{
    protected $buttons = array();
    
    function __construct()
    {
        parent::__construct();
    }

    public function addLink($name, $href, $type = 'default', $permission = false, $attributes = array())
    {
        if ($permission && !\checkPermission($permission))
            return false;
        
        if($href) {
            $attributes['href'] = $href;
        }
        $class = val('class', $attributes, null);
        $icon  = val('icon', $attributes);
        $class = trim('btn btn-'.$type.' '.$class);
        $attributes['class'] = $class;

        unset($attributes['icon']);

        $this->buttons[] = array(
            'type' => ($href ? 'a' : 'button'),
            'name' => $name,
            'icon' => $icon,
            'attributes' => implode_assoc('" ', '="', $attributes).'"'
        );
    }

    public function addButton($name, $type = 'default', $permission = false, $attributes = array())
    {
        $this->addLink($name, false, $type, $permission = false, $attributes);
    }

    public function toString()
    {
        $auth = \Garden\Gdn::factory('auth');

        $shortName = mb_strlen($auth->user->name) > 12 ? mb_substr($auth->user->name, 0, 12).'...' : $auth->user->name;

        $this->setData('buttons', $this->buttons);
        $this->setData('user', $auth->user);
        $this->setData('shortName', $shortName);

        return $this->fetchView('header', 'modules', 'dashboard');
    }
}