<?php
namespace Addons\Installer\Controllers;

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
        $this->install();
    }

    public function install()
    {
        $this->title('Installation');

        $this->render('install.php');
    }

}