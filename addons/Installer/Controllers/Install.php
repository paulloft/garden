<?php
namespace Addons\Installer\Controllers;

use Garden\Gdn;
use \Addons\Installer\Models as Model;

class Install extends \Garden\Template {

    public $template = 'install.php';

    public function initialize()
    {
        if ($this->renderType() == \Garden\Request::RENDER_ALL) {
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
        redirect('/install');
    }

    public function install()
    {
        $this->title('Installation');
        if (!c('main.install')) {
            $step = Gdn::request()->getQuery('step');
            $step = 'step_' . ($step > 0 && $step < 10 ? (int)$step : 1);

            \Garden\Cache::clear();

            $this->$step();
        } else {
            $this->render('installed.php');
        }
    }

    protected function step_1()
    {
        $this->render('step_1.php');
    }

    protected function step_2()
    {
        $model = Model\Install::instance();

        $form = $this->initForm();
        $form->validation()->rule('sitename', 'not_empty')->rule('locale', 'not_empty');

        $data = c('main');
        $form->setData($data);

        if ($form->submitted()) {
            if ($form->valid()) {
                $post = $form->getFormValues();
                $post['hashsalt'] = \Garden\SecureString::generateRandomKey(16);
                $post['logs'] = val('logs', $post) ? true : false;
                $post['debug'] = val('debug', $post) ? true : false;

                $model->saveConfig($post, 'main');

                redirect('/install?step=3');
            }
        }

        $this->render('step_2.php');
    }

    protected function step_3()
    {
        $this->addJs('step_3.js');
        $model = Model\Install::instance();
        $form = $this->initForm();

        $cacheDrivers = $model->cacheDrivers();

        $data = c('cache');
        $form->setData($data);

        if ($form->submitted()) {
            $post = $form->getFormValues();

            $driver = val('driver', $post);
            $options = val($driver, $post, []);

            try {
                $cache = \Garden\Cache::instance($driver, $options);
                $cache->add('test', 'test');
                $cache->get('test');
                $cache->delete('test');
            } catch (\Exception $exception) {
                $form->addError($exception->getMessage());
            }

            if ($form->valid()) {
                $model->saveConfig($post, 'cache');
                redirect('/install?step=4');
            }
        }

        $this->setData('cacheDrivers', $cacheDrivers);

        $this->render('step_3.php');
    }

    protected function step_4()
    {
        $model = Model\Install::instance();
        $form = $this->initForm();

        $data = c('database');
        $form->setData($data);

        if ($form->submitted()) {
            $post = $form->getFormValues();

            try {
                $db = \Garden\Db\Database::instance('test', $post);
                $db->connect();
            } catch (\Exception $exception) {
                $form->addError($exception->getMessage());
            }

            if ($form->valid()) {
                $model->saveConfig($post, 'database');
                redirect('/install?step=5');
            }
        }

        $this->render('step_4.php');
    }

    protected function step_5()
    {
        $model = Model\Install::instance();
        $form = $this->initForm();

        $data = c('addons');
        $form->setData($data);
        $addons = \Garden\Addons::all();

        if ($form->submitted()) {
            $post = $form->getFormValues();

            $install = val('addons', $post);
            $model->installAddons($install);

            if ($form->valid()) {
                $model->saveAddons($post);
                redirect('/install?step=6');
            }
        }

        $this->setData('addons', $addons);

        $this->render('step_5.php');
    }

    protected function step_6()
    {
        if (!\Garden\Addons::enabled('dashboard')) {
            redirect('/install?step=7');
        }


        $form = $this->initForm();

        $userModel = Gdn::users();
        $form->setModel($userModel);

        if ($form->submitted()) {
            $form->setFormValue('admin', 1);
            if ($id = $form->save()) {
                Gdn::auth()->forceLogin($id);
                redirect('/install?step=7');
            }
        }

        $this->render('step_6.php');
    }

    protected function step_7()
    {
        $model = Model\Install::instance();
        $model->saveConfig(['install' => true], 'main');

        $this->render('step_7.php');
    }

}