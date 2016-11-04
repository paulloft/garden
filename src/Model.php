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
    
    public $table;
    public $primaryKey = 'id';
    public $allowedFields = [];

    /**
     * @var int current user id
     */
    public $userID = null;

    /**
     * @var bool if true getWhere() returns data with object
     */
    public $resultObject = false;

    protected $_query;
    protected $_allowedFields = [];

    private $_insertFields = [];
    private $_updateFields = [];
    private $_insupdFields = [];
    private $_deleteFields = [];

    private $_b_table;

    /**
     * Class constructor. Defines the related database table name.
     * @param string $table table name
     */
    public function __construct($table = null)
    {
        $this->setTable($table);

        if (Gdn::factoryExists('auth')) {
            $user = Gdn::factory('auth')->user;
            $this->userID = val('id', $user);
        }

        $this->setFields($this->allowedFields);
    }

    /**
     * Set using table
     * @param string $table table name
     */
    public function setTable($table = null)
    {
        $this->table = $this->_b_table = $table;
    }

    public function switchTable($table = false)
    {
        if (!$this->_b_table) $this->_b_table = $this->table;
        $this->table = $table ?: $this->_b_table;            
    }

    /**
     * Get the data from the table based on its primary key
     *
     * @param ind $id Element ID
     * @return array|object
     */
    public function getID($id)
    {
        $query = DB::select('*')
            ->from($this->table)
            ->where($this->primaryKey, '=', $id)
            ->limit(1);

        return $query->execute(null, $this->resultObject)->current(); 
    }

    /**
     * Get a dataset for the table.
     *
     * @param array $order Array('order' => 'direction')
     * @param int $limit 
     * @param int $offset
     * @return Db\Database\Result
     */
    public function get($order = [], $limit = false, $offset = 0)
    {
        return $this->getWhere([], $order, $limit, $offset);
    }

    /**
     * Get a dataset for the table with a where filter.
     *
     * @param array $where Array('field' => 'value') .
     * @param array $order Array('order' => 'direction')
     * @param int $limit 
     * @param int $offset
     * @return Db\Database\Result
     */
    public function getWhere($where = [], $order = [], $limit = false, $offset = 0)
    {
        $this->_query = DB::select('*')->from($this->table);

        $this->_where($where);

        foreach ($order as $field => $direction) {
            $this->_query->order_by($field, $direction);
        }

        if ($limit !== false) {
            $this->_query->limit($limit);
            $this->_query->offset($offset);
        }

        return $this->_query->execute(null, $this->resultObject);
    }

    /**
     * get count of rows from table with a where filter.
     *
     * @param array $where Array('field' => 'value') .
     * @return int
     */

    public function getCount($where = [])
    {
        $count = DB::expr("COUNT({$this->primaryKey})");
        $this->_query = DB::select($count, 'count')
            ->from($this->table);

        $this->_where($where);

        return $this->_query->execute()->get('count');
    }

    /**
     * Sets the definition of the field for the table
     *
     * @param array $fields fields array
     */
    public function setFields($fields)
    {
        $this->_allowedFields[$this->table] = $fields;
    }

    /**
     * Return the definition of the field for the table
     *
     * @return array
     */
    public function getFields()
    {
        return val($this->table, $this->_allowedFields, []);
    }

    /**
     * Added to the datebase POST data 
     *
     * @param array $data inserted fields
     * @return int Record ID
     */
    public function insert($data)
    {
        $data = $this->insertDefaultFields($data);
        $data = $this->fixPostData($data);
        $columns = array_keys($data);

        $query = DB::insert($this->table, $columns)
            ->values($data)
            ->execute();

        return val(0, $query, false);
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

        DB::update($this->table)
            ->set($data)
            ->where($this->primaryKey, '=', $id)
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

        $this->_query = DB::update($this->table)
            ->set($data);

        $this->_where($where);

        $this->_query->execute();
    }

    /**
     * Кemoves fields are not part of the table
     *
     * @param array $post
     * @return array
     */
    public function fixPostData($post)
    {
        $fields = $this->getFields() ?: $this->getTableFields();
        return $this->checkArray($post, $fields);
    }

    /**
     * Returne table columns
     *
     * @return array
     */
    public function getTableFields()
    {
        $cacheKey = 'table_columns_'.$this->table;
        $result = Gdn::cache()->get($cacheKey);

        if (!$result) {
            $columns = DB::query(Database::SELECT, 'SHOW COLUMNS FROM `'.$this->table.'`')
                ->execute()
                ->as_array();


            $result = [];
            foreach ($columns as $col) {
                $result[] = $col['Field'];
            }

            Gdn::cache()->set($cacheKey, $result);
        }

        return $result;
    }

    /**
     * save post data into table
     *
     * @param array $post POST data
     * @param int $id record ID
     * @return int inserted or updated record ID
     */
    public function save($post, $id = false)
    {
        if ($id) {
            $this->update($id, $post);
        } else {
            $id = $this->insert($post);
        }

        return $id;
    }

    /**
     * removes records of the $where condition
     * @param array $where
     */
    public function delete($where = [])
    {
        $this->_query = DB::delete($this->table);
        $this->_where($where);
        $this->_query->execute();
    }

    /**
     * Delete record by primary key
     *
     * @param $id
     */
    public function deleteID($id)
    {
        $this->delete([$this->primaryKey => $id]);
    }


    /**
     * enqueues addition data
     * @param array $fields array fields
     */
    public function insert_queue($fields = [])
    {
        $fields = $this->insertDefaultFields($fields);
        $fields = $this->fixPostData($fields);
        if (!empty($fields)) {
            $this->_insertFields[] = $fields;
        }
    }

    /**
     * enqueues update data
     * @param array $fields
     * @param array $where
     */
    public function update_queue($fields, $where = [])
    {
        $fields = $this->updateDefaultFields($fields);
        $fields = $this->fixPostData($fields);
        if (!empty($fields)) {
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
        if (!empty($fields)) {
            $this->_insupdFields[] = $fields;
        }
    }

    /**
     * enqueues delete data
     * @param array $where
     */
    public function delete_queue($where)
    {
        if (!empty($where)) {
            $this->_deleteFields[] = $where;
        }
    }

    /**
     * start all pending operations
     *
     * @param string $table
     */
    public function start_queue($table = false)
    {
        $sql = "";
        $table = $table ?: $this->table;

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

        if (empty($sql)) 
            return;

        DB::query(null, $sql)->execute();

        $this->_updateFields = $this->_insertFields = $this->_deleteFields = [];
    }

    /**
     * Checks for a condition for uniqueness
     *
     * @param $where
     * @return bool
     */
    public function unique($where)
    {
        $query = DB::select(array('COUNT("*")', 'total_count'))->from($this->table);

        foreach ($where as $field => $value) {
            $query->where($field, '=', $value);
        }

        return (bool)$query->execute()->get('total_count');
    }

    /**
     * Converting date fields to sql format
     * @param array $post
     * @param array $fields
     * @return array converted post data
     */
    public function convertPostDate($post, $fields)
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        foreach ($fields as $field)  {
            $value = val($field, $post);

            if ($value !== false) {
                $new = date_convert($value, 'sql');
                $post[$field] = $new ?: null;
            }
        }

        return $post;
    }

    /**
     * Get table columns
     * @return array
     */
    public function getStructure()
    {
        return Gdn::database()->list_columns($this->table);
    }


    protected function insertDefaultFields($data)
    {
        if (!val('dateInserted', $data)) {
            $data['dateInserted'] = DB::expr('now()');
        }

        if (!val('userInserted', $data)) {
            $data['userInserted'] = $this->userID;
        }

        return $data;
    }

    protected function updateDefaultFields($data)
    {
        if (!val('dateUpdated', $data)) {
            $data['dateUpdated'] = DB::expr('now()');
        }

        if (!val('userUpdated', $data)) {
            $data['userUpdated'] = $this->userID;
        }

        return $data;
    }

    protected function _where($field, $value = null) 
    {
        if (!is_array($field))
            $field = array($field => $value);

        foreach ($field as $subField => $subValue) {
            if (is_array($subValue) && empty($subValue)) 
                continue;

            $expr = $this->conditionExpr($subField, $subValue);
            $this->_query->where($expr[0], $expr[1], $expr[2]);
        }
        return $this;
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
        $result = [];
        foreach ($array as $key => $value) {
            if (in_array($key, $fields))
                $result[$key] = $value;
        }

        return $result;
    }

    protected function conditionExpr($field, $value)
    {
        $expr = ''; // final expression which is built up
        $op = ''; // logical operator

        // Try and split an operator out of $Field.
        $fieldOpRegex = "/(?:\s*(=|<>|>|<|>=|<=)\s*$)|\s+(like|not\s+like)\s*$|\s+(?:(is)\s+(null)|(is\s+not)\s+(null)|(not\s+in))\s*$/i";
        $split = preg_split($fieldOpRegex, $field, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if (count($split) > 1) {
            list($field, $op) = $split;

            if (count($split) > 2) {
                $value = null;
            }
        } else {
            $op = '=';
        }

        if ($op == '=' && is_null($value)) {
            // this is a special case where the value sql is checking for an is null operation.
            $op = 'is';
            $value = null;
        }

        if ($op != 'not in' AND is_array($value)) {
            $op = 'in';
        }

        return array($field, $op, $value);
    }

}