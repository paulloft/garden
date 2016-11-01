<?php
namespace Addons\Dashboard\Controllers;
use Addons\Dashboard\Models as Model;
use Garden\Gdn;
/**
* 
*/
class Dashboard extends Base
{
    
    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->pageInit();
        $this->title('Dashboard');

        $this->render();
    }

    public function structure()
    {
        $this->permission('dashboard.admin');

        $this->pageInit();
        $this->title('Update database structure');
        $this->currentUrl('/dashboard/structure');

        $captureOnly = Gdn::request()->getQuery('update', false) === false;

        $structure = Gdn::structure();
        $permission = Gdn::factory('permission');
        $structure->capture = $captureOnly;
        $permission->captureOnly = $captureOnly;

        foreach (\Garden\Addons::enabled() as $addon => $options) {
            $dir = val('dir', $options);
            $file = $dir.'/settings/structure.php';
            if (file_exists($file)) {
                include_once $file;
            }
        }

        $permission->save();

        $capture = $structure->capture();

        if (!$captureOnly) {
            redirect('/dashboard/structure');
        }

        $this->setData('capturePerm', $permission->capture);
        $this->setData('capturedSql', $capture);

        $this->render();
    }

}