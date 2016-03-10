<?php 
namespace Garden;
use Garden\Db\Database;

/**
 * Model base class
 * 
 *
 * @author PaulLoft <info@paulloft.ru>
 * @copyright 2014 Paulloft
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package Garden
 */

class Model extends Plugin {
    
    public $_table;
    public $_primary_key = 'id';
    public $_allowedFields = array();

    public $user_id = null;

    public $resultObject = true;

    protected $_query;

    protected $_insertFields = array();
    protected $_updateFields = array();
    protected $_insupdFields = array();
    protected $_deleteFields = array();

    protected $_b_table;


    /**
     * Class constructor. Defines the related database table name.
     * @param string $table table name
     */
    public function __construct($table = null)
    {
        $this->setTable($table);
        //TODO: Доделать 
        // $user = Auth::instance()->get_user();
        $user = false;
        if($user) {
            $this->user_id = $user->id;
        }

        $this->_allowedFields[$table] = $this->_allowedFields;
    }

    /**
     * Set using table
     * @param string $table table name
     */
    public function setTable($table = null)
    {
        $this->_table = $this->_b_table = $table;
    }

    public function switchTable($table = false)
    {
        if(!$this->_b_table) $this->_b_table = $this->_table;
        $this->_table = $table ?: $this->_b_table;            
    }

    /**
     * Get the data from the table based on its primary key
     *
     * @param ind $id Element ID
     * @return PDO_DataSet
     */
    public function getID($id)
    {
        $query = DB::select('*')
            ->from($this->_table)
            ->where($this->_primary_key, '=', $id)
            ->limit(1);

        return $query->as_object()->execute()->current(); 
    }

    /**
     * Get a dataset for the table with a where filter.
     *
     * @param array $where Array('field' => 'value') .
     * @param array $order Array('order' => 'direction')
     * @param int $limit 
     * @param int $offset
     * @return PDO_DataSet
     */
    public function getWhere($where = array(), $order = array(), $limit = false, $offset = 0)
    {
        $this->_query = DB::select('*')->from($this->_table);

        $this->_where($where);

        foreach ($order as $field => $direction) {
            $this->_query->order_by($field, $direction);
        }

        if($limit !== false) {
            $this->_query->limit($limit);
            $this->_query->offset($offset);
        }

        return  $this->resultObject 
            ? $this->_query->as_object()->execute() 
            : $this->_query->execute()->as_array(); 
    }

    public function getCount($where = array())
    {
        $this->_query = DB::select('*')->from($this->_table);

        $this->_where($where);

        return $this->_query->execute()->count();
    }

    /**
     * Sets the definition of the field for the table
     *
     * @param array $fields fields array
     */
    public function setFields($fields)
    {
        $this->_allowedFields[$this->_table] = $fields;
    }

    /**
     * Added to the datebase POST data 
     *
     * @param array $data Element ID
     * @return Record ID
     */
    public function insert($data)
    {
        $data = $this->insertDefaultFields($data);
        $data = $this->fixPostData($data);
        $columns = array_keys($data);

        $query = DB::insert($this->_table, $columns)
            ->values($data)
            ->execute();

        return val(0, $query, false);
    }


    protected function insertDefaultFields($data)
    {
        if(!val('date_inserted', $data)) {
            $data['date_inserted'] = DB::expr('now()');
        }

        if(!val('user_inserted', $data)) {
            $data['user_inserted'] = $this->user_id;
        }

        return $data;
    }

    protected function updateDefaultFields($data)
    {
        if(!val('date_update', $data)) {
            $data['date_update'] = DB::expr('now()');
        }

        if(!val('user_update', $data)) {
            $data['user_update'] = $this->user_id;
        }

        return $data;
    }

    /**
     * Update record by ID
     *
     * @param int $id Element ID
     * @param array $data POST data
     */
    public function update($id, $data)
    {
        $data = $this->updateDefaultFields($data);
        $data = $this->fixPostData($data);

        DB::update($this->_table)
            ->set($data)
            ->where($this->_primary_key, '=', $id)
            ->execute();
    }

    /**
     * Update record by filter
     *
     * @param array $where Array('field' => 'value')
     * @param array $data POST data
     */
    public function updateWhere($where, $data)
    {
        $data = $this->updateDefaultFields($data);
        $data = $this->fixPostData($data);

        $this->_query = DB::update($this->_table)
            ->set($data);

        $this->_where($where);

        $this->_query->execute();
    }

    /**
     * Clears the $array of extra
     *
     * @param array $array
     * @param array $fields POST data
     * @return fixed array
     */
    protected function checkArray($array, $fields)
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (in_array($key, $fields))
                $result[$key] = $value;
        }

        return $result;
    }

    public function fixPostData($post)
    {
        $fields = empty($this->_allowedFields[$this->_table]) ? $this->getTableFields() : $this->_allowedFields[$this->_table];
        return $this->checkArray($post, $fields);
    }

    public function getTableFields()
    {
        $columns = DB::query(Database::SELECT, 'SHOW COLUMNS FROM `'.$this->_table.'`')
            ->execute()
            ->as_array();


        $result = array();
        foreach ($columns as $col) {
            $result[] = $col['Field'];
        }

        return $result;
    }

    public function _where($Field, $Value = NULL) 
    {
        if (!is_array($Field))
            $Field = array($Field => $Value);

        foreach ($Field as $SubField => $SubValue) {
            if(is_array($SubValue) && empty($SubValue)) 
                continue;

            $Expr = $this->conditionExpr($SubField, $SubValue);
            $this->_query->where($Expr[0], $Expr[1], $Expr[2]);
        }
        return $this;
    }

    protected function conditionExpr($Field, $Value)
    {
        $Expr = ''; // final expression which is built up
        $Op = ''; // logical operator

        // Try and split an operator out of $Field.
        $FieldOpRegex = "/(?:\s*(=|<>|>|<|>=|<=)\s*$)|\s+(like|not\s+like)\s*$|\s+(?:(is)\s+(null)|(is\s+not)\s+(null))\s*$/i";
        $Split = preg_split($FieldOpRegex, $Field, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if (count($Split) > 1) {
            list($Field, $Op) = $Split;

            if (count($Split) > 2) {
                $Value = null;
            }
        } else {
            $Op = '=';
        }

        if ($Op == '=' && is_null($Value)) {
            // This is a special case where the value SQL is checking for an is null operation.
            $Op = 'is';
            $Value = null;
        }

        if (is_array($Value)) {
            $Op = 'in';
        }

        return array($Field, $Op, $Value);
    }

    /**
     * save post data into table
     *
     * @param array $post POST data
     * @param int $id record ID
     * @return inserted or updated record ID
     */
    public function save($post, $id = false)
    {
        if($id) {
            $this->update($id, $post);
        } else {
            $id = $this->insert($post);
        }

        return $id;
    }

    public function delete($where = array())
    {
        $this->_query = DB::delete($this->_table);
        $this->_where($where);
        $this->_query->execute();
    }


    /**
     * enqueues additionn data
     * @param array $fields array fields
     */
    public function insert_queue($fields = array())
    {
        $fields = $this->insertDefaultFields($fields);
        $fields = $this->fixPostData($fields);
        if(!empty($fields)) {
            $this->_insertFields[] = $fields;
        }
    }

    /**
     * enqueues update data
     * @param array $fields
     * @param array $where
     */
    public function update_queue($fields, $where = array())
    {
        $fields = $this->updateDefaultFields($fields);
        $fields = $this->fixPostData($fields);
        if(!empty($fields)) {
            $this->_updateFields[] = array('fields' => $fields, 'where' => $where);
        }
    }

    /**
     * enqueues insert or update data
     * @param array $fields
     * @param array $where
     */
    public function insupd_queue($fields)
    {
        $fields = $this->updateDefaultFields($fields);
        $fields = $this->fixPostData($fields);
        if(!empty($fields)) {
            $this->_insupdFields[] = $fields;
        }
    }

    /**
     * enqueues delete data
     * @param array $where
     */
    public function delete_queue($where)
    {
        if(!empty($where)) {
            $this->_deleteFields[] = $where;
        }
    }

    /**
     * start all pending operations
     */
    public function start_queue($table = false)
    {
        $sql = "";
        $table = $table ?: $this->_table;

        foreach($this->_updateFields as $update) {
            $fields = val('fields', $update);
            $where  = val('where', $update);
            $this->_query = DB::update($table)->set($fields);
            $this->_where($where);
            $sql .= $this->_query->compile().";\n";
        }
        
        foreach($this->_insertFields as $fields) {
            $columns = array_keys($fields);
            $sql .= DB::insert($table, $columns)->values($fields)->compile().";\n";
        }

        foreach($this->_insupdFields as $fields) {
            $columns = array_keys($fields);
            $insert = DB::insert($table, $columns)->values($fields)->compile();
            $update = str_replace('  SET', '', DB::update()->set($fields)->compile());

            $sql .= $insert." ON DUPLICATE KEY ".$update.";\n";
        }

        foreach($this->_deleteFields as $delete) {
            $this->_query = DB::delete($table);
            $this->_where($delete);
            $sql .= $this->_query->compile().";\n";
        }

        if(empty($sql)) 
            return;

        DB::query(null, $sql)->execute();

        $this->_updateFields = $this->_insertFields = $this->_deleteFields = array();
    }

    public function unique($where)
    {
        $query = DB::select(array('COUNT("*")', 'total_count'))->from($this->_table);

        foreach ($where as $field => $value) {
            $query->where($field, '=', $value);
        }

        return (bool)$query->execute()->get('total_count');
    }

    public function convertPostDate($post, $fields)
    {
        if(!is_array($fields)) {
            $fields = array($fields);
        }

        foreach ($fields as $field)  {
            $value = val($field, $post);

            if($value !== false) {
                $new = Date::convDate($value, 'sql');
                $post[$field] = $new ?: null;
            }
        }

        return $post;
    }

}