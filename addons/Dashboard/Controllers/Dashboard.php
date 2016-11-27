<?php
namespace Addons\Dashboard\Controllers;

use Addons\Dashboard\Models as Model;
use Garden\Gdn;

class Dashboard extends Base {

    public function initialize()
    {
        $this->pageInit();
    }

    public function index()
    {
        $this->title('Dashboard');

        $this->render();
    }

    public function structure()
    {
        $this->permission('dashboard.admin');
        $this->title('Update database structure');

        $captureOnly = Gdn::request()->getQuery('update', false) === false;

        $structure = Gdn::structure();
        $permission = Model\Permission::instance();
        $structure->capture = $captureOnly;
        $permission->captureOnly = $captureOnly;

        foreach (\Garden\Addons::enabled() as $addon => $options) {
            $dir = val('dir', $options);
            $file = $dir . '/settings/structure.php';
            if (file_exists($file)) {
                include_once $file;
            }
        }

        if (!$captureOnly) {
            redirect('/dashboard/structure');
        }

        $this->setData('capturePerm', $permission->capture);
        $this->setData('capturedSql', $structure->capture());

        $this->render();
    }

    public function settings()
    {
        $this->permission('dashboard.admin');
        $this->title('System settings');

        $form = $this->form();
        $form->validation()
            ->rule('sitename', 'not_empty')
            ->rule('locale', 'not_empty');

        $data = c('main');
        $form->setData($data);

        if ($form->submitted()) {
            if ($form->valid()) {
                $post = $form->getFormValues();
                $post['logs'] = val('logs', $post) ? true : false;
                $post['debug'] = val('debug', $post) ? true : false;

                \Garden\Config::save($post, 'main');
//                \Garden\Cache::clear();
            }
        }
        $locales = [
            'en_US' => '[en_US] English',
            'ru_RU' => '[ru_RU] Русский'
        ];

        $this->setData('locales', $locales);
        $this->render();
    }

    public function addons()
    {
        $this->permission('dashboard.admin');
        $this->title('Addon manager');

        $form = $this->form();

        $data = c('addons');
        $form->setData($data);
        $addons = \Garden\Addons::all();

        if ($form->submitted()) {
            $post = $form->getFormValues();

            if ($form->valid()) {

            }
        }

        $this->setData('addons', $addons);
        $this->render();
    }

}