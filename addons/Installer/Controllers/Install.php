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
use Garden\Form;
use Garden\Renderers\Template;
use Garden\Request;
use Garden\Response;
use Garden\SecureString;

class Install {

    public $template = 'install.php';

    /**
     * Instalator template
     *
     * @return Template
     */
    protected function template(): Template
    {
        $template = new Template('install.php');
        $template
            ->setTitle('Installation')
            ->addCss('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css')
            ->addCss('//fonts.googleapis.com/css?family=Open+Sans:300,400,400italic,600,700')
            ->addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css')
            ->addCss('bootstrap.theme.css')
            ->addCss('install.css')
            ->addJs('//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js')
            ->addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');

        return $template;
    }

    public function index()
    {
        Response::current()->redirect('/install');
    }

    public function install(): Template
    {
        if (Config::get('main.install')) {
            return $this->template()->setView('installed.php');
        }

        $step = Request::current()->getQuery('step');
        $step = 'step_' . ($step > 0 && $step < 10 ? (int)$step : 1);

        Cache::clear();

        $data = $this->$step();

        return $this->template()
            ->setView("$step.php")
            ->setDataArray($data);
    }

    protected function step_1(): array
    {
        return [];
    }

    protected function step_2(): array
    {
        $form = new Form();
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

        return ['form' => $form];
    }

    protected function step_3(): array
    {
        $data = Config::get('cache');
        $form = new Form(null, $data);

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

        $this->template()->addJs('step_3.js');

        return [
            'form' => $form,
            'cacheDrivers' => Model\Install::instance()->cacheDrivers()
        ];
    }

    protected function step_4(): array
    {
        $form = new Form();

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

        return ['form' => $form];
    }

    protected function step_5(): array
    {
        $model = Model\Install::instance();
        $form = new Form();

        $data = Config::get('addons');
        $form->setData($data);

        if ($form->submitted()) {
            $install = $form->getFormValues();
            $model->installAddons($install);

            if ($form->valid()) {
                $model->saveAddons($install);
                Response::current()->redirect('/install?step=6');
            }
        }

        return [
            'addons' => Addons::all(),
            'form' => $form,
        ];
    }

    protected function step_6(): array
    {
        if (!Addons::enabled('dashboard')) {
            Response::current()->redirect('/install?step=7');
        }

        $form = new Form(Users::instance());

        if ($form->submitted()) {
            $form->setFormValue('admin', 1);
            $id = $form->save();

            if ($id) {
                Auth::instance()->forceLogin($id);
                Response::current()->redirect('/install?step=7');
            }
        }

        return ['form' => $form];
    }

    protected function step_7(): array
    {
        Config::save(['install' => true], 'main');

        return [];
    }

}