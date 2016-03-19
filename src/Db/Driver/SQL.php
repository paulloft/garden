<?php
namespace Garden\Db\Driver;
use \Garden\Exception as Exception;
use \Garden\Db\Database;


abstract class SQL extends Database {

    public function datatype($type)
    {
        static $types = array
        (
            'blob'                      => array('type' => 'string', 'binary' => TRUE, 'maxLength' => '65535'),
            'bool'                      => array('type' => 'bool'),
            'bigint unsigned'           => array('type' => 'int', 'min' => '0', 'max' => '18446744073709551615'),
            'datetime'                  => array('type' => 'string'),
            'decimal unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
            'double'                    => array('type' => 'float'),
            'double precision unsigned' => array('type' => 'float', 'min' => '0'),
            'double unsigned'           => array('type' => 'float', 'min' => '0'),
            'enum'                      => array('type' => 'string'),
            'fixed'                     => array('type' => 'float', 'exact' => TRUE),
            'fixed unsigned'            => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
            'float unsigned'            => array('type' => 'float', 'min' => '0'),
            'geometry'                  => array('type' => 'string', 'binary' => TRUE),
            'int unsigned'              => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
            'integer unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
            'longblob'                  => array('type' => 'string', 'binary' => TRUE, 'maxLength' => '4294967295'),
            'longtext'                  => array('type' => 'string', 'maxLength' => '4294967295'),
            'mediumblob'                => array('type' => 'string', 'binary' => TRUE, 'maxLength' => '16777215'),
            'mediumint'                 => array('type' => 'int', 'min' => '-8388608', 'max' => '8388607'),
            'mediumint unsigned'        => array('type' => 'int', 'min' => '0', 'max' => '16777215'),
            'mediumtext'                => array('type' => 'string', 'maxLength' => '16777215'),
            'national varchar'          => array('type' => 'string'),
            'numeric unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
            'nvarchar'                  => array('type' => 'string'),
            'point'                     => array('type' => 'string', 'binary' => TRUE),
            'real unsigned'             => array('type' => 'float', 'min' => '0'),
            'set'                       => array('type' => 'string'),
            'smallint unsigned'         => array('type' => 'int', 'min' => '0', 'max' => '65535'),
            'text'                      => array('type' => 'string', 'maxLength' => '65535'),
            'tinyblob'                  => array('type' => 'string', 'binary' => TRUE, 'maxLength' => '255'),
            'tinyint'                   => array('type' => 'int', 'min' => '-128', 'max' => '127'),
            'tinyint unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '255'),
            'tinytext'                  => array('type' => 'string', 'maxLength' => '255'),
            'year'                      => array('type' => 'string'),
        );

        $type = str_replace(' zerofill', '', $type);

        if (isset($types[$type]))
            return $types[$type];

        return parent::datatype($type);
    }

    public function list_tables($like = NULL)
    {
        if (is_string($like))
        {
            // Search for table names
            $result = $this->query(Database::SELECT, 'SHOW TABLES LIKE '.$this->quote($like), FALSE);
        }
        else
        {
            // Find all table names
            $result = $this->query(Database::SELECT, 'SHOW TABLES', FALSE);
        }

        $tables = array();
        foreach ($result as $row)
        {
            $tables[] = reset($row);
        }

        return $tables;
    }

    public function list_columns($table, $like = NULL, $add_prefix = TRUE)
    {
        // Quote the table name
        $table = ($add_prefix === TRUE) ? $this->quote_table($table) : $table;

        if (is_string($like))
        {
            // Search for column names
            $result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table.' LIKE '.$this->quote($like), FALSE);
        }
        else
        {
            // Find all column names
            $result = $this->query(Database::SELECT, 'SHOW FULL COLUMNS FROM '.$table, FALSE);
        }

        $count = 0;
        $columns = array();
        foreach ($result as $row)
        {
            list($type, $length) = $this->_parse_type($row['Type']);

            $column = (object)$this->datatype($type);

            $column->name      = $row['Field'];
            $column->default   = $row['Default'];
            $column->dataType  = $type;
            $column->allowNull = ($row['Null'] == 'YES');
            $column->position  = ++$count;
            $column->length    = $length;

            switch ($column->type)
            {
                case 'float':
                    if (isset($length))
                    {
                        list($column->numPrecision, $column->numScale) = explode(',', $length);
                    }
                break;
                case 'int':
                    if (isset($length))
                    {
                        // MySQL attribute
                        $column->length = $length;
                    }
                break;
                case 'string':
                    switch ($column->dataType)
                    {
                        case 'binary':
                        case 'varbinary':
                            $column->maxLength = $length;
                        break;
                        case 'char':
                        case 'varchar':
                            $column->maxLength = $length;
                        case 'text':
                        case 'tinytext':
                        case 'mediumtext':
                        case 'longtext':
                            $column->collation = $row['Collation'];
                        break;
                        case 'enum':
                        case 'set':
                            $column->collation = $row['Collation'];
                            $column->options = explode('\',\'', substr($length, 1, -1));
                        break;
                    }
                break;
            }

            // MySQL attributes
            $column->comment       = $row['Comment'];
            $column->autoIncrement = strpos($row['Extra'], 'auto_increment') === FALSE ? FALSE : TRUE;;
            $column->key           = $row['Key'];
            $column->privileges    = $row['Privileges'];

            $columns[$row['Field']] = $column;
        }

        return $columns;
    }

}