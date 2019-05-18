<?php

namespace Addons\Installer\Controllers;

use Addons\Dashboard\Models\Auth;
use Addons\Dashboard\Models\Users;
use Exception;
use Garden\Addons;
use Garden\Cache;
use Garden\Config;
use Garden\Db\Database;
use Addons\Installer\Models as Model;
use Garden\Request;
use Garden\Response;
use Garden\SecureString;
use Garden\Template;

class Install extends Template {

    public $template = 'install.php';

    public function initialize()
    {
        if ($this->renderType() === Request::RENDER_ALL) {
            $this->addCss('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
            $this->addCss('//fonts.googleapis.com/css?family=Open+Sans:300,400,400italic,600,700');
            $this->addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
            $this->addCss('bootstrap.theme.css');

            $this->addJs('//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js');
            $this->addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');

            $this->addCss('install.css');
        }
    }

    public function index()
    {
        Response::current()->redirect('/install');
    }

    public function install()
    {
        $this->title('Installation');
        if (Config::get('main.install')) {
            $this->render('installed.php');
        } else {
            $step = Request::current()->getQuery('step');
            $step = 'step_' . ($step > 0 && $step < 10 ? (int)$step : 1);

            Cache::clear();

            $this->$step();
        }
    }

    protected function step_1()
    {
        $this->render('step_1.php');
    }

    protected function step_2()
    {
        $form = $this->form();
        $form->validation()
            ->rule('sitename', 'required')
            ->rule('locale', 'required');

        $data = Config::get('main');
        $form->setData($data);

        if ($form->submitted() && $form->valid()) {
            $post = $form->getFormValues();
            $post['hashsalt'] = SecureString::generateRandomKey(16);
            $post['logs'] = val('logs', $post) ? true : false;
            $post['debug'] = val('debug', $post) ? true : false;

            Config::save($post, 'main');

            Response::current()->redirect('/install?step=3');
        }

        $this->render('step_2.php');
    }

    protected function step_3()
    {
        $this->addJs('step_3.js');
        $model = Model\Install::instance();
        $form = $this->form();

        $cacheDrivers = $model->cacheDrivers();

        $data = Config::get('cache');
        $form->setData($data);

        if ($form->submitted()) {
            $post = $form->getFormValues();

            $driver = val('driver', $post);
            $options = val($driver, $post, []);

            try {
                $cache = Cache::instance($driver, $options);
                $cache->add('test', 'test');
                $cache->get('test');
                $cache->delete('test');
            } catch (Exception $exception) {
                $form->addError($exception->getMessage());
            }

            if ($form->valid()) {
                Config::save($post, 'cache');
                Response::current()->redirect('/install?step=4');
            }
        }

        $this->setData('cacheDrivers', $cacheDrivers);

        $this->render('step_3.php');
    }

    protected function step_4()
    {
        $form = $this->form();

        $data = Config::get('database');
        $form->setData($data);

        if ($form->submitted()) {
            $post = $form->getFormValues();

            try {
                $db = Database::instance('test', $post);
                $db->connect();
            } catch (Exception $exception) {
                $form->addError($exception->getMessage());
            }

            if ($form->valid()) {
                Config::save($post, 'database');
                Response::current()->redirect('/install?step=5');
            }
        }

        $this->render('step_4.php');
    }

    protected function step_5()
    {
        $model = Model\Install::instance();
        $form = $this->form();

        $data = Config::get('addons');
        $form->setData($data);
        $addons = Addons::all();

        if ($form->submitted()) {
            $install = $form->getFormValues();
            $model->installAddons($install);

            if ($form->valid()) {
                $model->saveAddons($install);
                Response::current()->redirect('/install?step=6');
            }
        }

        $this->setData('addons', $addons);

        $this->render('step_5.php');
    }

    protected function step_6()
    {
        if (!Addons::enabled('dashboard')) {
            Response::current()->redirect('/install?step=7');
        }


        $form = $this->form();

        $userModel = Users::instance();
        $form->setModel($userModel);

        if ($form->submitted()) {
            $form->setFormValue('admin', 1);
            $id = $form->save();

            if ($id) {
                Auth::instance()->forceLogin($id);
                Response::current()->redirect('/install?step=7');
            }
        }

        $this->render('step_6.php');
    }

    protected function step_7()
    {
        Config::save(['install' => true], 'main');

        $this->render('step_7.php');
    }

}