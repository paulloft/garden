<?php
namespace Addons\Skeleton\Controllers;
use Addons\Skeleton\Models as Model;
use Garden\Gdn;
/**
* 
*/
class Skeleton extends \Garden\Template
{

    protected function pageInit()
    {
        $this->addJs('//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js', false);
        $this->addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', false);

        $this->addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css', false);
        $this->addCss('starter-template.css');

        $this->meta('X-UA-Compatible', 'IE=edge,chrome=1', true);
        $this->meta('viewport', 'width=device-width, initial-scale=1.0');
    }

    public function index()
    {
        $this->pageInit();
        $this->title('Hello World');

        $this->render();
    }

    public function about()
    {
        $this->pageInit();
        $this->title('About Garden');

        $this->render('index');
    }

    public function contact()
    {
        $this->pageInit();
        $this->title('Contact form');

        $form = $this->form();
        $form->validation()
             ->rule('name', 'required')
             ->rule('email', 'email');

        if ($form->submitted()) {
            $form->save();
        }

        $this->render();
    }

}