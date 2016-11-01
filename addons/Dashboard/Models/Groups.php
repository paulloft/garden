<?php
namespace Addons\Dashboard\Models;
use Garden\Db;

class Groups extends \Garden\Model
{
    public $table = 'groups';
    
    function __construct()
    {
        parent::__construct($this->table);
    }

    public function getID($id)
    {
        $result = $this->getWhere(array($this->primaryKey=>$id, 'deleted'=>0))->current();

        return $result;
    }
     
    public function getWhere($where = array(), $order = array(), $limit = false, $offset = 0)
    {
        $where['deleted'] = 0;
        return parent::getWhere($where, $order, $limit, $offset);
    }

    public function delete($where = array())
    {
        $this->_query = DB::update($this->table)
            ->set([
                'deleted' => 1,
                'dateDeleted' => DB::expr('now()')
            ]);

        $this->_where($where);  
        $this->_query->execute();
    }
    
}