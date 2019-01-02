<?php

namespace Addons\Dashboard\Hooks;

use Addons\Dashboard\Models;
use Addons\Dashboard\Controllers;
use Addons\Dashboard\Modules\Header as HeaderModule;
use Addons\Dashboard\Modules\Sidebar;
use Garden\Traits\Instance;
use Garden\Translate;

/**
 * Dashboard hooks
 */
class Dashboard
{

    use Instance;

    public function dispatch_handler()
    {
        Models\Auth::instance()->autoLogin();
    }

    /**
     * @param $exception \Garden\Exception\Client
     * @throws \Garden\Exception\NotFound
     * @return bool|string
     */
    public function exception_handler($exception)
    {
        $code = $exception->getCode();
        if (in_array($code, [400, 401, 403, 404])) {
            $template = new Controllers\Base(false);
            $template->title($exception->getMessage());
            $template->setData('description', $exception->getDescription());
            $template->setData('code', $code);
            $template->pageInit();
            $template->template('empty');

            return $template->fetchTemplate($code, 'exception');
        }

        return false;
    }

    public function dashboard_page_init_handler()
    {
        $sidebar = Sidebar::instance();

        $sidebar->addGroup('dashboard', Translate::get('Dashboard'), '/dashboard', 10, 'dashboard', ['icon' => 'fa fa-dashboard']);

        $sidebar->addGroup('users', Translate::get('Users'), false, 20, false, ['icon' => 'fa fa-user']);
        $sidebar->addItem('users', Translate::get('Users'), '/dashboard/users', 10, 'dashboard.user.view');
        $sidebar->addItem('users', Translate::get('User groups'), '/dashboard/users/groups', 20, 'dashboard.group.view');

        $sidebar->addGroup('system', Translate::get('System settings'), false, 500, false, ['icon' => 'fa fa-cog']);
        $sidebar->addItem('system', Translate::get('Addons'), '/dashboard/addons', 10, 'dashboard.admin');
        $sidebar->addItem('system', Translate::get('System settings'), '/dashboard/settings', 20, 'dashboard.admin');
        $sidebar->addItem('system', Translate::get('Update database'), '/dashboard/structure', 30, 'dashboard.admin');
        $sidebar->addItem('system', Translate::get('Error log'), '/dashboard/errorlog', 40, 'dashboard.admin');

        HeaderModule::instance()->addLink(Translate::get('Clear cache'), '?nocache', 'success', false, ['icon' => 'fa fa-refresh']);
    }

    public function install()
    {

    }
}