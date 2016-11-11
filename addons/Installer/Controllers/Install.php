<?php
namespace Addons\Installer\Controllers;

use Garden\Gdn;

class Install extends \Garden\Template  {

    public $template = 'install.php';

    public function initialize()
    {
        if($this->renderType() == \Garden\Request::RENDER_ALL) {
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
        $step = Gdn::request()->getQuery('step');

        Gdn::cache('system')->deleteAll();

        switch ($step) {
            case 2:
                $this->step_2();
                break;

            default:
                $this->step_1();
                break;
        }


    }

    protected function step_1()
    {
        $this->render('step_1.php');
    }

    protected function step_2()
    {
        $form = $this->initForm();
        $form->validation()
            ->rule('name', 'not_empty')
            ->rule('locale', 'not_empty');

        $mainConf = c('main');

        $form->setData($mainConf);

        if ($form->submitted()) {
            $data = $form->getFormValues();


            $mainConf['sitename'] = val('sitename', $data);
            $mainConf['hashsalt'] = \Garden\SecureString::generateRandomKey(16);
            $mainConf['locale'] = val('locale', $data, 'en_US');
            $mainConf['logs'] = val('logs', $data) ? true : false;
            $mainConf['debug'] = val('debug', $data) ? true : false;

            array_save($mainConf, GDN_CONF.'/main.php', 777);
        }

        $this->render('step_2.php');
    }

}