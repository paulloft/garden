<?php
namespace Addons\Dashboard\Hooks;

use Addons\Dashboard\Models;
use Addons\Dashboard\Controllers;
/**
 * Dashboard hooks
 */
class Dashboard extends \Garden\Plugin {

    public function dispatch_handler()
    {
        Models\Auth::instance()->autoLogin();
    }

    /**
     * @param $exception \Garden\Exception\Client
     * @return bool|string
     */
    public function exception_handler($exception)
    {
        $code = $exception->getCode();
        if(in_array($code, [400, 401, 403, 404])) {
            $template = new Controllers\Base(false);
            $template->title($exception->getMessage());
            $template->setData('description', $exception->getDescription());
            $template->setData('code', $code);
            $template->pageInit();
            $template->template('empty');

            return $template->fetchTemplate($code, 'exception');
        } else {
            return false;
        }
    }

    public function dashboard_page_init_handler()
    {
        $sidebar = \SidebarModule::instance();

        $sidebar->addGroup('dashboard', t('Dashboard'), '/dashboard', 10, 'dashboard', ['icon' => 'fa fa-dashboard']);

        $sidebar->addGroup('users', t('Users'), false, 20, false, ['icon' => 'fa fa-user']);
        $sidebar->addItem('users', t('Users'), '/dashboard/users', 10, 'dashboard.user.view');
        $sidebar->addItem('users', t('User groups'), '/dashboard/users/groups', 20, 'dashboard.group.view');

        $sidebar->addGroup('system', t('System settings'), false, 500, false, ['icon' => 'fa fa-cog']);
        $sidebar->addItem('system', t('Addons'), '/dashboard/addons', 10, 'dashboard.admin');
        $sidebar->addItem('system', t('Update database'), '/dashboard/structure', 30, 'dashboard.admin');
        $sidebar->addItem('system', t('System settings'), '/dashboard/settings', 20, 'dashboard.admin');

        \HeaderModule::instance()->addLink('Clear cache', '?nocache', 'success', false, ['icon' => 'fa fa-refresh']);
    }

    public function install()
    {

    }
}