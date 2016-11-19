<?php
namespace Addons\Skeleton\Models;
use Garden\DB;

/**
* 
*/
class Table extends \Garden\Model
{
    public $table = 'php_st';

    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function test()
    {
        return DB::select('*')
            ->from('php_st')
            ->where('id', '<', 5)
            ->execute();
    }
}