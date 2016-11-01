<?php
namespace Addons\Dashboard;
use Garden\Gdn;
/**
 * Dashboard hooks
 */
class Hooks extends \Garden\Plugin
{
    public function dispatch_handler()
    {
        Models\Auth::instance()->autoLogin();
    }
    
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

    public function dashboardPageInit_handler()
    {
        $sidebar = \SidebarModule::instance();

        $sidebar->addGroup('dashboard', 'Dashboard', '/dashboard', 10, 'dashboard', ['icon' => 'fa fa-dashboard']);

        $sidebar->addGroup('users', 'Users', false, 20, 'dashboard', ['icon' => 'fa fa-user']);
        $sidebar->addItem('users', 'Users', '/users', 10, 'dashboard.user.view');
        $sidebar->addItem('users', 'User groups', '/users/groups', 20, 'dashboard.group.view');

        $sidebar->addGroup('system', 'System settings', false, 500, 'dashboard', ['icon' => 'fa fa-cog']);
        $sidebar->addItem('system', 'Update database', '/dashboard/structure', 300, 'dashboard.admin');

        \HeaderModule::instance()->addLink('Clear cache', '?nocache', 'success', false, array('icon' => 'fa fa-refresh'));
    }
}