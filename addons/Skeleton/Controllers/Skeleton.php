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
        // $structure = Gdn::database()->list_columns('map_tooth');
        // d($structure);
        $s = Gdn::structure();
        $s->CaptureOnly = 1;
        include PATH_ADDONS.'/Skeleton/structure.php';
        d($s->Database->CapturedSql);
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