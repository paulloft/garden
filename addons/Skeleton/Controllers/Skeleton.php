<?php
namespace Addons\Skeleton\Controllers;
use Addons\Skeleton\Models as Model;
use Garden\Gdn;
/**
* 
*/
class Skeleton extends \Garden\Template
{
    
    function __construct()
    {
        parent::__construct();
    }

    protected function pageInit() {
        $this->addJs('jquery.min.js', '//ajax.googleapis.com/ajax/libs/jquery/2.1.0');
        $this->addJs('bootstrap.min.js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js');

        $this->addCss('bootstrap.min.css', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css');
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

    public function about() {
        $this->pageInit();
        $this->title('About Garden');

        $this->render('index');
    }

    public function structure()
    {
        $this->pageInit();
        $this->title('Structure update');

        $captureOnly = Gdn::request()->getQuery('update', false) === false;

        $structure = Gdn::structure();
        $structure->capture = $captureOnly;
        include PATH_ADDONS.'/Skeleton/structure.php';

        $capture = $structure->capture();
        if(!$captureOnly) {
            redirect('/structure');
        }
        // d($capture);

        $this->setData('capturedSql', $capture);

        $this->render();
    }

    public function test()
    {

        $tableModel = Model\Table::instance();

        $s = $tableModel->test();

        p($s);


        $cache = Gdn::cache()->add('test', $s, 10);

        $result = Gdn::cache()->get('test', false);

        
        
    }

    


}