<?php
namespace Addons\Skeleton\Controllers;
use Addons\Skeleton\Models as Model;
use Garden\Gdn;
/**
* 
*/
class Api extends \Garden\Template
{
    
    function __construct() {
        parent::__construct();
    }

    public function index() {
        echo 'index method_';
    }

    public function test($id, $bi = false) {
        $this->title('Тестировый тайтл');
        $this->addJs('jquery.min.js', '//ajax.googleapis.com/ajax/libs/jquery/2.1.0');
        $this->addCss('bootstrap', false);
        $this->meta('X-UA-Compatible', 'IE=edge,chrome=1', true);
        $this->meta('viewport', 'width=device-width, initial-scale=1.0');

        $tableModel = Model\Table::instance();

        $s = $tableModel->test();

        p($s);


        $cache = Gdn::cache()->add('test', $s, 10);

        $result = Gdn::cache()->get('test', false);

        
        $this->render('index');
    }

    


}