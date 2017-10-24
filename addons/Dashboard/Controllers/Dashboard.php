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
        $permission = Gdn::permission();
        $structure->capture = $captureOnly;
        $permission->captureOnly = $captureOnly;

        $addons = \Garden\Addons::all();
        $enabled = \Garden\Addons::enabled();

        foreach ($addons as $addon => $options) {
            $addonEnabled = val($addon, $enabled) ? true : false;
            $permission->addonEnabled = $addonEnabled;
            $structure->addonEnabled = $addonEnabled;
            $dir = val('dir', $options);
            $file = $dir.'/Settings/structure.php';
            if (file_exists($file)) {
                include_once $file;
            }
        }

        $permission->save();

        $capture = $structure->capture();

        if (!$captureOnly) {
            \Garden\Cache::clear();
            redirect('/dashboard/structure');
        }

        $this->setData('capturePerm', $permission->capture);
        $this->setData('capturedSql', $capture);

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

        if ($form->submitted() && $form->valid()) {
            $post = $form->getFormValues();
            $post['logs'] = val('logs', $post) ? true : false;
            $post['debug'] = val('debug', $post) ? true : false;

            \Garden\Config::save($post, 'main');
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
        $model = Model\Addons::instance();
        $addons = $model->getAll();

        if ($form->submitted()) {
            $name = $form->getFormValue('addon');
            $enable = $form->getFormValue('enable');

            if ($enable && !$model->install($name)) {
                $form->addError($model->error);
            } else {
                $model->save($name, $enable);
                \Garden\Cache::clear();
                redirect('/dashboard/addons');
            }
        }

        $this->setData('addons', $addons);
        $this->render();
    }

    public function errorlog()
    {
        $this->permission('dashboard.admin');
        $this->title('Error log');
        $date = strtotime(Gdn::request()->getQuery('date', 'now'));

        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (.*|.*\n.*) in file (.+) on line (\d+)/u';
        $file = GDN_LOGS.'/'.date('Y', $date).'/'.date('m', $date).'/'.date('d', $date).'.log';
        $content = file_get_contents($file);

        $matches = [];
        preg_match_all($pattern, $content, $matches);

        $count = count($matches[0]) - 1;

        $result = [];
        for ($i = $count; $i >= 0; $i--) {
            $result[] = [
                'date' => $matches[1][$i],
                'text' => $matches[2][$i],
                'file' => str_replace([PATH_ROOT,'/'], ['', '/<wbr>'], $matches[3][$i]),
                'line' => $matches[4][$i],
            ];
        }

        $this->setData('date', date_convert($date, 'Y-m-d'));
        $this->setData('data', $result);
        $this->render();
    }
}