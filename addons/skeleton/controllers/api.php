<?php
namespace Garden;

/**
* 
*/
class ApiController extends Plugin
{
    
    function __construct() {
        echo 'construct_';
    }

    public function index() {
        echo 'index method_';
    }

    public function test() {
        p($_REQUEST);
        // d(c('Database'));
        // Gdn::getInstance();
        // Gdn::instance();
        // Gdn::factory();
        // Factory::get();
        // Instance::get();
        // Gdn::database();
        // Instance::database();
        // Factory::database();

        $db = Gdn::database();

        $result = $db->sql()->get('php_st', array('id'=>'5'));

        d($result);

        d($db, $db->sql(), $db->sql()->pdo());

        // Gdn::factory('Usermodel');

        // Factory::get('Usermodel');
        // Usermodel::instance();
    }


}