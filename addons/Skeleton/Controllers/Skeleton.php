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
        $this->addJs('//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js', false, true);
        $this->addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', false, true);

        $this->addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css', false, true);
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
             ->rule('name', 'not_empty')
             ->rule('email', 'email');

        if ($form->submitted()) {
            $form->save();
        }

        $this->render();
    }

    public function structure()
    {
        $this->pageInit();
        $this->title('Update structure');

        $captureOnly = Gdn::request()->getQuery('update', false) === false;

        $structure = Gdn::structure();
        // $permission = Factory::get('permission');
        $structure->capture = $captureOnly;
        // $permission->captureOnly = $captureOnly;

        foreach (\Garden\Addons::enabled() as $addon => $options) {
            $dir = val('dir', $options);
            $file = $dir.'/settings/structure.php';
            if (file_exists($file)) {
                include_once $file;
            }
        }

        // $permission->save();

        $capture = $structure->capture();

        // $this->setData('capturePerm', $permission->capture);
        $this->setData('capturedSql', $capture);

        $this->render();
    }

}