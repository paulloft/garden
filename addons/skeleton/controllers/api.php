<?php
namespace Garden;
use Garden\Db\DB;
/**
* 
*/
class ApiController extends Plugin
{
    
    function __construct() {
        // echo 'construct_';
    }

    public function index() {
        echo 'index method_';
    }

    public function test() {
        // p($_REQUEST);
        // d(c('Database'));
        // Gdn::getInstance();
        // Gdn::instance();
        // Gdn::factory();
        // Factory::get();
        // Instance::get();
        // Gdn::database();
        // Instance::database();
        // Factory::database();

        // $db = Gdn::database();

        $s = DB::select('*')
            ->from('php_st')
            ->where('id', '<', 5)
            ->execute();

        // d($s);
        
        // $result = $db->sql()->get('php_st', array('id'=>'5'));
        // $result = $db->sql()
        //     ->select('*')
        //     ->from('php_st')
        //     ->where('id<', 5)
        //     ->get();
        // d($result->Result());

        // d($db, $db->sql());

        // Gdn::factory('Usermodel');

        // Factory::get('Usermodel');
        // Usermodel::instance();

        $cache = Gdn::cache()->add('test', $s, 10);

        $result = Gdn::cache()->get('test', false);
        d($result);
    }


}