<?php

namespace Addons\Dashboard\Controllers;

use Addons\Dashboard\Models as Model;
use Garden\Addons;
use Garden\Cache;
use Garden\Config;
use Addons\Dashboard\Models\Db\Structure;
use Garden\Form;
use Garden\Helpers\Date;
use Garden\Renderers\Template;
use Garden\Request;
use Garden\Response;
use Garden\Exception;
use function count;

class Dashboard extends Model\Page
{

    /**
     * Dashboard main page
     *
     * @return Template
     * @throws Exception\Forbidden
     */
    public function index(): Template
    {
        $this->permission('dashboard.admin');

        return Model\Template::get()->setTitle('Dashboard');
    }

    /**
     * Update DB structure
     *
     * @return Template
     * @throws Exception\Forbidden
     */
    public function structure(): Template
    {
        $this->permission('dashboard.admin');

        $captureOnly = Request::current()->getQuery('update', false) === false;

        $structure = Structure::instance();
        $permission = Model\Permission::instance();
        $structure->capture = $captureOnly;
        $permission->captureOnly = $captureOnly;

        $addons = Addons::all();
        $enabled = Addons::enabled();

        foreach ($addons as $addon => $options) {
            $addonEnabled = isset($enabled[$addon]);
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

        return Model\Template::get()
            ->setTitle('Update database structure')
            ->setData('capturePerm', $permission->capture)
            ->setData('capturedSql', $capture);
    }

    /**
     * Settings page
     *
     * @return Template
     * @throws Exception\Forbidden
     */
    public function settings(): Template
    {
        $this->permission('dashboard.admin');

        $form = new Form();
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

        return Model\Template::get()
            ->setTitle('System settings')
            ->setData('form', $form)
            ->setData('locales', [
                'en_US' => '[en_US] English',
                'ru_RU' => '[ru_RU] Русский'
            ]);
    }

    /**
     * Addons page
     *
     * @return Template
     * @throws Exception\Forbidden
     */
    public function addons(): Template
    {
        $this->permission('dashboard.admin');

        $form = new Form();

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

        return Model\Template::get()
            ->setTitle('Addon manager')
            ->setData('form', $form)
            ->setData('addons', $addons);
    }

    /**
     * Error logs page
     *
     * @return Template
     * @throws Exception\Forbidden
     */
    public function errorlog(): Template
    {
        $this->permission('dashboard.admin');
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
                'line' => $matches[4][$i]
            ];
        }

        return Model\Template::get()
            ->setTitle('Error log')
            ->setData('date', Date::createTimestamp($timestamp)->toSql(false))
            ->setData('data', $result);
    }
}