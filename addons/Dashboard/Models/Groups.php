<?php

namespace Addons\Dashboard\Models;

use Garden\Db\DB;

class Groups extends \Garden\Model
{
    public $table = 'groups';

    public function getID($id)
    {
        return $this->getWhere([$this->primaryKey => $id, 'deleted' => 0])->current();
    }

    public function getWhere(array $where = [], array $order = [], $limit = false, $offset = 0)
    {
        $where['deleted'] = 0;
        return parent::getWhere($where, $order, $limit, $offset);
    }

    public function delete(array $where = [])
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