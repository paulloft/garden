<?php
namespace Addons\Dashboard;
use Garden\Gdn;
/**
 * Dashboard hooks
 */
class Hooks extends \Garden\Plugin {

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

    public function dashboard_page_init_handler()
    {
        $sidebar = \SidebarModule::instance();

        $sidebar->addGroup('dashboard', 'Dashboard', '/dashboard', 10, 'dashboard', ['icon' => 'fa fa-dashboard']);

        $sidebar->addGroup('users', 'Users', false, 20, false, ['icon' => 'fa fa-user']);
        $sidebar->addItem('users', 'Users', '/dashboard/users', 10, 'dashboard.user.view');
        $sidebar->addItem('users', 'User groups', '/dashboard/users/groups', 20, 'dashboard.group.view');

        $sidebar->addGroup('system', 'System settings', false, 500, false, ['icon' => 'fa fa-cog']);
        $sidebar->addItem('system', 'Update database', '/dashboard/structure', 300, 'dashboard.admin');

        \HeaderModule::instance()->addLink('Clear cache', '?nocache', 'success', false, array('icon' => 'fa fa-refresh'));
    }

    public function permission_update_handler()
    {
        $permission = Factory::get('permission');

        $permission
            ->define('dashboard.user.view')
            ->define('dashboard.user.add')
            ->define('dashboard.user.edit')
            ->define('dashboard.user.delete')

            ->define('dashboard.group.view')
            ->define('dashboard.group.add')
            ->define('dashboard.group.edit')
            ->define('dashboard.group.delete')
        ;
    }

    public function install()
    {

    }
}