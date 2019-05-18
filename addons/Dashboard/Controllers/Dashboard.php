<?php

namespace Addons\Dashboard\Controllers;

use Addons\Dashboard\Models as Model;
use Garden\Addons;
use Garden\Cache;
use Garden\Config;
use Garden\Db\Structure;
use Garden\Helpers\Date;
use Garden\Request;
use Garden\Response;
use function count;

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

        $captureOnly = Request::current()->getQuery('update', false) === false;

        $structure = Structure::instance();
        $permission = Model\Permission::instance();
        $structure->capture = $captureOnly;
        $permission->captureOnly = $captureOnly;

        $addons = Addons::all();
        $enabled = Addons::enabled();

        foreach ($addons as $addon => $options) {
            $addonEnabled = val($addon, $enabled) ? true : false;
            $permission->addonEnabled = $addonEnabled;
            $structure->addonEnabled = $addonEnabled;
            $dir = $options['dir'] ?? '';
            $file = "$dir/Settings/structure.php";
            if (file_exists($file)) {
                include_once $file;
            }
        }

        $permission->save();

        $capture = $structure->capture();

        if (!$captureOnly) {
            Cache::clear();
            Response::current()->redirect('/dashboard/structure');
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
            ->rule('sitename', 'required')
            ->rule('locale', 'required');

        $data = Config::get('main');
        $form->setData($data);

        if ($form->submitted() && $form->valid()) {
            $post = $form->getFormValues();
            $post['logs'] = val('logs', $post) ? true : false;
            $post['debug'] = val('debug', $post) ? true : false;

            Config::save($post, 'main');
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

        $data = Config::get('addons');
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
                Cache::clear();
                Response::current()->redirect('/dashboard/addons');
            }
        }

        $this->setData('addons', $addons);
        $this->render();
    }

    public function errorlog()
    {
        $this->permission('dashboard.admin');
        $this->title('Error log');
        $timestamp = strtotime(Request::current()->getQuery('date', 'now'));

        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (.*|.*\n.*) in file (.+) on line (\d+)/u';
        $file = GDN_LOGS . '/' . date('Y', $timestamp) . '/' . date('m', $timestamp) . '/' . date('d', $timestamp) . '.log';
        $content = file_get_contents($file);

        $matches = [];
        preg_match_all($pattern, $content, $matches);

        $count = count($matches[0]) - 1;

        $result = [];
        for ($i = $count; $i >= 0; $i--) {
            $result[] = [
                'date' => $matches[1][$i],
                'text' => $matches[2][$i],
                'file' => str_replace([PATH_ROOT, '/'], ['', '/<wbr>'], $matches[3][$i]),
                'line' => $matches[4][$i],
            ];
        }

        $this->setData('date', Date::createTimestamp($timestamp)->toSql(false));
        $this->setData('data', $result);
        $this->render();
    }
}