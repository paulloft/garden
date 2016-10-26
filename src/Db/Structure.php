<?php
namespace Garden\Db;

use \Garden\Exception as Exception;
use \Garden\Db\Database;
use \Garden\Gdn;

/**
 * Database Structure tools
 *
 * Used by any given database driver to build, modify, and create tables and views.
 *
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2003 Vanilla Forums, Inc
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package Garden
 * @since 2.0
 */
abstract class Structure
{

    public $database;
    public $capture = false;

    protected $_sql = array();
    protected $_prefix = '';
    protected $_encoding;
    protected $_columns;
    protected $_existingColumns = null;
    protected $_table;
    protected $_tableExists;
    protected $_engine;

    public static $instances = array();

    /**
     * @param null $name
     * @return $this
     */
    public static function instance($name = null)
    {
        if ($name === null) {
            // Use the default Database instance name
            $name = Database::$default;
        }

        if (!isset(Structure::$instances[$name])) {
            $database = Gdn::database($name);

            Structure::$instances[$name] = new Structure\MySQL($database);
        }

        return Structure::$instances[$name];
    }

    /**
     * The constructor for this class. Automatically fills $this->classname.
     *
     * @param string $database
     * @todo $database needs a description.
     */
    public function __construct($database = null)
    {
        if (is_null($database)) {
            $this->database = Gdn::database();
        } else {
            $this->database = $database;
        }

        $this->prefix($this->database->tablePrefix());

        $this->reset();
    }

    /**
     * Specifies the name of the table to create or modify.
     *
     * @param string $name The name of the table.
     * @param string $encoding The default character encoding to specify for this table.
     * @return $this $this.
     */
    public function table($name = '', $encoding = '')
    {
        if (!$name) {
            return $this->_table;
        }

        $this->_table = $name;
        if ($encoding == '') {
            $encoding = $this->database->encoding();
        }

        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Defines a primary key column on a table.
     *
     * @param string $name The name of the column.
     * @param string $type The data type of the column.
     * @return $this.
     */
    public function primary($name, $type = 'int(10)')
    {
        $column = $this->createColumn($name, $type, false, null, 'primary');
        $dataType = val('type', $this->dataType($column));

        if ($dataType === 'int') {
            $column->autoIncrement = true;
        }
        $this->_columns[$name] = $column;

        return $this;
    }

    /**
     * Defines a column to be added to $this->table().
     *
     * @param string $name The name of the column to create.
     * @param mixed $type The data type of the column to be created. Types with a length speecifty the length in barackets.
     * * If an array of values is provided, the type will be set as "enum" and the array will be assigned as the column's Enum property.
     * * If an array of two values is specified then a "set" or "enum" can be specified (ex. array('set', array('short', 'tall', 'fat', 'skinny')))
     * @param boolean $nullDefault Whether or not nulls are allowed, if not a default can be specified.
     * * true: Nulls are allowed.
     * * false: Nulls are not allowed.
     * * Any other value: Nulls are not allowed, and the specified value will be used as the default.
     * @param string $keyType What type of key is this column on the table? Options
     * are primary, key, and false (not a key).
     * @return $this.
     */
    public function column($name, $type, $nullDefault = false, $keyType = false)
    {
        if (is_null($nullDefault) || $nullDefault === true) {
            $null = true;
            $default = null;
        } elseif ($nullDefault === false) {
            $null = false;
            $default = null;
        } elseif (is_array($nullDefault)) {
            $null = val('null', $nullDefault);
            $default = val('default', $nullDefault, null);
        } else {
            $null = false;
            $default = $nullDefault;
        }

        // Check the key type for validity. A column can be in many keys by specifying an array as key type.
        $keyTypes = (array)$keyType;
        $keyTypes1 = array();
        foreach ($keyTypes as $keyType1) {
            $parts = explode('.', $keyType1, 2);

            if (in_array($parts[0], array('primary', 'key', 'index', 'unique', 'fulltext', false)))
                $keyTypes1[] = $keyType1;
        }
        if (count($keyTypes1) == 0) {
            $keyType = false;
        } elseif (count($keyTypes1) == 1) {
            $keyType = $keyTypes1[0];
        } else {
            $keyType = $keyTypes1;
        }

        $column = $this->createColumn($name, $type, $null, $default, $keyType);
        $this->_columns[$name] = $column;
        return $this;
    }

    /**
     * Creates the table and columns specified with $this->table() and
     * $this->column(). If no table or columns have been specified, this method
     * will throw a fatal error.
     *
     * @param boolean $explicit If TRUE, and the table specified with $this->table() already exists, this
     * method will remove any columns from the table that were not defined with
     * $this->column().
     * @param boolean $drop If TRUE, and the table specified with $this->table() already exists, this
     * method will drop the table before attempting to re-create it.
     */
    public function set($explicit = false, $drop = false)
    {
        /// Throw an event so that the structure can be overridden.
        \Garden\Event::fire('beforeSet', $explicit, $drop);

        try {
            // Make sure that table and columns have been defined
            if ($this->_table == '') {
                throw new Exception\Custom(t('You must specify a table before calling DatabaseStructure::Set()'));
            }

            if (count($this->_columns) == 0) {
                throw new Exception\Custom(t('You must provide at least one column before calling DatabaseStructure::Set()'));
            }

            if ($this->tableExists()) {
                if ($drop) {
                    // Drop the table.
                    $this->dropTable();

                    // And re-create it.
                    return $this->create();
                }

                // If the table already exists, go into modify mode.
                return $this->modify($explicit, $drop);
            } else {
                // If it doesn't already exist, go into create mode.
                return $this->create();
            }
        } catch (Exception\Custom $ex) {
            $this->reset();
            throw $ex;
        }
    }

    /**
     * Send a query to the database and return the result.
     * @param string $sql The sql to execute.
     * @return bool Whethor or not the query succeeded.
     */
    public function query($sql, $type = Database::INSERT)
    {
        if ($this->capture) {
            $this->_sql[] = $sql;
            return true;
        } else {
            return $this->database->query($type, $sql);
        }
    }

    /**
     * Gets and/or sets the database prefix.
     *
     * @param string $prefix
     * @todo $prefix needs a description.
     */
    public function prefix($prefix = '')
    {
        if ($prefix != '') $this->_prefix = $prefix;

        return $this->_prefix;
    }

    /** Whether or not the table exists in the database.
     * @return bool
     */
    public function tableExists($tablename = null)
    {
        if ($this->_tableExists === null || $tablename !== null) {
            if ($tablename === null) {
                $tablename = $this->tableName();
            }

            if (strlen($tablename) > 0) {
                $tables = $this->database->list_tables($this->_prefix . $tablename);
                $result = count($tables) > 0;
            } else {
                $result = false;
            }
            if ($tablename == $this->tableName())
                $this->_tableExists = $result;
            return $result;
        }
        return $this->_tableExists;
    }

    /** Returns the name of the table being defined in this object.
     *
     * @return string
     */
    public function tableName()
    {
        return $this->_table;
    }

    /** Returns whether or not a column exists in the database.
     *
     * @param string $columnName The name of the column to check.
     * @return bool
     */
    public function columnExists($columnName)
    {
        $columns = $this->existingColumns();
        $result = isset($columns[$columnName]);
        if (!$result) {
            foreach ($columns as $colName => $def) {
                if (strcasecmp($columnName, $colName) == 0) return true;
            }
            return false;
        }
        return $result;
    }

    /** Gets the column definitions for the columns in the database.
     * @return array
     */
    public function existingColumns()
    {
        if ($this->_existingColumns === null) {
            if ($this->tableExists()) {
                $this->_existingColumns = $this->getColumns($this->_table);
            } else {
                $this->_existingColumns = array();
            }
        }
        return $this->_existingColumns;
    }

    /**
     * And associative array of $columnName => $columnProperties columns for the table.
     * @return array
     */
    public function columns($name = '')
    {
        if (strlen($name) > 0) {
            if (isset($this->_columns[$name])) {
                return $this->_columns[$name];
            } else {
                foreach ($this->_columns as $colname => $def) {
                    if (strcasecmp($name, $colname) == 0)
                        return $def;
                }
                return null;
            }
        }
        return $this->_columns;
    }

    /** Load the schema for this table from the database.
     * @param string $tablename The name of the table to get or blank to get the schema for the current table.
     * @return $this
     */
    public function get($tablename = '')
    {
        if ($tablename) {
            $this->table($tablename);
        }

        $columns = $this->getColumns($this->_table);
        $this->_columns = $columns;

        return $this;
    }

    /** Return the definition string for a column.
     * @param mixed $column The column to get the type string from.
     *  - <b>object</b>: The column as returned by the database schema. The properties looked at are Type, Length, and Precision.
     *  - <b>string</b<: The name of the column currently in this structure.
     * * @return string The type definition string.
     */
    public function columnType($column)
    {
        if (is_string($column))
            $column = $this->_columns[$column];

        $type = val('type', $column);
        $length = val('length', $column);
        $precision = val('precision', $column);

        if (in_array(strtolower($type), array('tinyint', 'smallint', 'mediumint', 'int', 'float', 'double'))) {
            $length = null;
        }

        if ($type && $length && $precision) {
            $result = "$type($length, $precision)";
        } elseif ($type && $length) {
            $result = "$type($length)";
        } elseif (strtolower($type) == 'enum') {
            $result = val('enum', $column, array());
        } elseif ($type) {
            $result = $type;
        } else {
            $result = 'int';
        }

        return $result;
    }

    /** Gets an arrya of type names allowed in the structure.
     * @param string $class The class of types to get. Valid values are:
     *  - <b>int</b>: Integer types.
     *  - <b>float</b>: Floating point types.
     *  - <b>decimal</b>: Precise decimal types.
     *  - <b>numeric</b>: float, int and decimal.
     *  - <b>string</b>: String types.
     *  - <b>date</b>: Date types.
     *  - <b>length</b>: Types that have a length.
     *  - <b>precision</b>: Types that have a precision.
     *  - <b>other</b>: Types that don't fit into any other category on their own.
     *  - <b>all</b>: All recognized types.
     */
    public function types($class = 'all')
    {
        $date = array('datetime', 'date');
        $decimal = array('decimal', 'numeric');
        $float = array('float', 'double');
        $int = array('int', 'tinyint', 'smallint', 'mediumint', 'bigint');
        $string = array('varchar', 'char', 'mediumtext', 'text');
        $length = array('varbinary');
        $other = array('enum', 'tinyblob', 'blob', 'mediumblob', 'longblob');

        switch (strtolower($class)) {
            case 'date':
                return $date;
            case 'decimal':
                return $decimal;
            case 'float':
                return $float;
            case 'int':
                return $int;
            case 'string':
                return $string;
            case 'other':
                return array_merge($length, $other);

            case 'numeric':
                return array_merge($float, $int, $decimal);
            case 'length':
                return array_merge($string, $length, $decimal);
            case 'precision':
                return $decimal;

            default:
                return array();
        }
    }

    /**
     * Return captured sql query.
     */
    public function capture()
    {
        return $this->_sql;
    }


    /** Reset the internal state of this object so that it can be reused.
     * @return $this
     */
    public function reset()
    {
        $this->_encoding = '';
        $this->_columns = array();
        $this->_existingColumns = null;
        $this->_tableExists = null;
        $this->_table = '';
        $this->_engine = null;

        return $this;
    }

    protected function createColumn($name, $type, $null, $default, $keyType)
    {
        $length = '';
        $precision = '';

        // Check to see if the type starts with a 'u' for unsigned.
        if (is_string($type) && strncasecmp($type, 'u', 1) == 0) {
            $type = substr($type, 1);
            $unsigned = true;
        } else {
            $unsigned = false;
        }

        // Check for a length in the type.
        if (is_string($type) && preg_match('/(\w+)\s*\(\s*(\d+)\s*(?:,\s*(\d+)\s*)?\)/', $type, $matches)) {
            $type = $matches[1];
            $length = $matches[2];
            if (count($matches) >= 4)
                $precision = $matches[3];
        }

        $column = new \stdClass();
        $column->name = $name;
        $column->type = is_array($type) ? 'enum' : $type;
        $column->length = $length;
        $column->precision = $precision;
        $column->enum = is_array($type) ? $type : false;
        $column->allowNull = $null;
        $column->default = $default;
        $column->keyType = $keyType;
        $column->unsigned = $unsigned;
        $column->autoIncrement = false;

        // Handle enums and sets as types.
        if (is_array($type)) {
            if (count($type) === 2 && is_array(val(1, $type))) {
                // The type is specified as the first element in the array.
                $column->type = $type[0];
                $column->enum = $type[1];
            } else {
                // This is an enum.
                $column->type = 'enum';
                $column->enum = $type;
            }
        } else {
            $column->type = $type;
            $column->enum = false;
        }

        return $column;
    }

    protected function getKeyType($key)
    {
        switch ($key) {
            case 'PRI':
                return 'primary';
                break;
            case 'UNI':
                return 'uniqid';
                break;
            case 'MUL':
                return 'index';
                break;

            default:
                return false;
                break;
        }
    }

    protected function getColumns($table)
    {
        $columns = $this->database->list_columns($table);

        $result = array();
        foreach ($columns as $name => $column) {
            if (is_string($column->type) && strncasecmp($column->type, 'u', 1) == 0) {
                $unsigned = true;
            } else {
                $unsigned = false;
            }

            $obj = new \stdClass();
            $obj->name = $column->name;
            $obj->type = $column->dataType;
            $obj->length = $column->length;
            $obj->precision = '';
            $obj->enum = val('options', $column);
            $obj->allowNull = $column->allowNull;
            $obj->default = $column->default;
            $obj->keyType = $this->getKeyType($column->key);
            $obj->unsigned = $unsigned;
            $obj->autoIncrement = $column->autoIncrement;

            $result[$name] = $obj;
        }

        return $result;
    }

    protected function _query($sql)
    {
        return $this->database->query(Database::SELECT, $sql, true);
    }

    protected function dataType($column)
    {
        return $this->database->datatype($column->type);
    }

    /**
     * Drops $this->table() from the database.
     */
    abstract public function dropTable();

    /**
     * Drops $name column from $this->table().
     *
     * @param string $name The name of the column to drop from $this->table().
     */
    abstract public function dropColumn($name);

    /**
     * Renames a column in $this->table().
     *
     * @param string $olDName The name of the column to be renamed.
     * @param string $newName The new name for the column being renamed.
     * @param string $tableName
     * @return boolean
     */
    abstract public function renameColumn($olDName, $newName, $tablename = '');

    /**
     * Renames a table in the database.
     *
     * @param string $olDName The name of the table to be renamed.
     * @param string $newName The new name for the table being renamed.
     * before $olDName and $newName.
     */
    abstract public function renameTable($olDName, $newName);

    /**
     * Specifies the name of the view to create or modify.
     *
     * @param string $name The name of the view.
     * @param string $query The actual query to create as the view. Typically this
     * can be generated with the $database object.
     */
    abstract public function view($name, $query);

    /**
     * Specifies the engine of the table to create or modify.
     *
     * @param string $engine The name of engine.
     * @param boolean $checkAvailability If TRUE check engine availability
     * @return $this.
     */
    abstract public function engine($engine, $checkAvailability = true);

    /**
     * check engine availability.
     *
     * @param string $engine The name of engine.
     * @return boolean.
     */
    abstract public function hasEngine($engine);

    /**
     * Modifies $this->table() with the columns specified with $this->column().
     *
     * @param boolean $explicit If true, this method will remove any columns from the table that were not
     * defined with $this->column().
     */
    abstract protected function modify($explicit = false);

    /**
     * Creates the table defined with $this->table() and $this->column().
     */
    abstract protected function create();

}