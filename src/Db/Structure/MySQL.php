<?php
namespace Garden\Db\Structure;

use \Garden\Exception as Exception;
use \Garden\Db\Database;

/**
 * MySQL structure driver
 *
 * MySQL-specific structure tools for performing structural changes on MySQL
 * database servers.
 *
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2003 Vanilla Forums, Inc
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package Garden
 * @since 2.0
 */
class MySQL extends \Garden\Db\Structure
{

    public function __construct($database = null)
    {
        parent::__construct($database);
    }

    public function dropTable()
    {
        if ($this->tableExists()) {
            return $this->query('drop table `' . $this->_prefix . $this->_table . '`');
        }
    }

    public function dropColumn($name)
    {
        if (!$this->query('alter table `' . $this->_prefix . $this->_table . '` drop column `' . $name . '`')) {
            throw new Exception\Custom(t('Failed to remove the `%1$s` column from the `%2$s` table.'), array($name, $this->_prefix . $this->_table));
        }

        return true;
    }

    public function renameColumn($oldname, $newname, $tablename = '')
    {
        if ($tablename != '') $this->_table = $tablename;

        // Get the schema for this table
        $schema = $this->getColumns($this->_table);
        $oldColumn = val($oldname, $schema);
        $newColumn = val($newname, $schema);

        // Make sure that one column, or the other exists
        if (!$oldColumn && !$newColumn) {
            throw new Exception\Custom(t('The `%1$s` column does not exist.'), $oldname);
        }

        // Make sure the new column name isn't already taken
        if ($oldColumn && $newColumn) {
            throw new Exception\Custom(t('You cannot rename the `%1$s` column to `%2$s` because that column already exists.'), array($oldname, $newname));
        }

        // Rename the column
        if (!$this->query('alter table `' . $this->_table . '` change column `' . $oldname . '` `' . $newname . '` ' . $this->defineColumn($oldColumn))) {
            throw new Exception\Custom(t('Failed to rename table `%1$s` to `%2$s`.'), array($oldname, $newname));
        }

        return true;
    }

    public function renameTable($oldname, $newname)
    {
        if (!$this->query('rename table `' . $this->_prefix . $oldname . '` to `' . $this->_prefix . $newname . '`')) {
            throw new Exception\Custom(t('Failed to rename table `%1$s` to `%2$s`.'), array($oldname, $newname));
        }

        return true;
    }

    public function view($name, $sql)
    {
        $this->query('create or replace view ' . $this->_prefix . $name . " as \n" . $sql);
    }

    public function engine($engine, $checkAvailability = true)
    {
        $engine = strtolower($engine);

        if ($checkAvailability) {
            if (!$this->hasEngine($engine)) return $this;
        }


        $this->_engine = $engine;
        return $this;
    }

    public function hasEngine($engine)
    {
        $engine = strtolower($engine);
        static $viableEngines = null;

        if ($viableEngines === null) {
            $list = $this->_query("SHOW ENGINES;");
            $viableEngines = array();
            while ($storage = $list->get('Engine')) {
                $name = strtolower($storage);
                $viableEngines[$name] = true;
                $list->next();
            }
        }

        return isset($viableEngines[$engine]);
    }

    /**
     * Modifies $this->table() with the columns specified with $this->column().
     *
     * @param boolean $explicit If true, this method will remove any columns from the table that were not
     * defined with $this->column().
     */
    protected function modify($explicit = false)
    {
        $px = $this->_prefix;
        $addSql = array(); // statements executed at the end

        $existingColumns = $this->existingColumns();
        $alterSql = array();

        // 1. Remove any unnecessary columns if this is an explicit modification
        if ($explicit) {
            // array_diff returns values from the first array that aren't present
            // in the second array. In this example, all columns currently in the
            // table that are NOT in $this->_columns.
            $removeColumns = array_diff(array_keys($existingColumns), array_keys($this->_columns));
            foreach ($removeColumns as $column) {
                $alterSql[] = "drop column `$column`";
            }
        }

        // Prepare the alter query
        $alterSqlPrefix = 'alter table `' . $this->_prefix . $this->_table . "`\n";

        // 2. Alter the table storage engine.
        $forceEngine = c('database.forceStorageEngine', false);
        if ($forceEngine && !$this->_engine) {
            $this->_engine = $forceEngine;
        }
        $indexes = $this->indexSql($this->_columns);
        $indexesDb = $this->indexSqlDb();

        if ($this->_engine) {
            $currentEngine = $this->_query("show table status where name = '" . $this->_prefix . $this->_table . "'")->get('Engine');

            if (strcasecmp($currentEngine, $this->_engine)) {
                // Check to drop a fulltext index if we don't support it.
                if (!$this->supportsFulltext()) {
                    foreach ($indexesDb as $indexName => $indexSql) {
                        if (str_begins($indexSql, 'fulltext')) {
                            //Drop index query
                            if (!$this->query("$alterSqlPrefix drop index $indexName;\n")) {
                                throw new Exception\Custom(t('Failed to drop the index `%1$s` on table `%2$s`.'), array($indexName, $this->_table));
                            }
                        }
                    }
                }

                // Engine query
                if (!$this->query($alterSqlPrefix . ' engine = ' . $this->_engine)) {
                    throw new Exception\Custom(t('Failed to alter the storage engine of table `%1$s` to `%2$s`.'), array($this->_prefix . $this->_table, $this->_engine));
                }
            }
        }

        // 3. Add new columns & modify existing ones

        // array_diff returns values from the first array that aren't present in
        // the second array. In this example, all columns in $this->_columns that
        // are NOT in the table.
        $prevColumnName = false;
        foreach ($this->_columns as $columnName => $column) {
            if (!isset($existingColumns[$columnName])) {

                // This column name is not in the existing column collection, so add the column
                $addColumnSql = 'add ' . $this->defineColumn(val($columnName, $this->_columns));
                if ($prevColumnName !== false) {
                    $addColumnSql .= " after `$prevColumnName`";
                } else {
                    $addColumnSql .= " first";
                }

                $alterSql[] = $addColumnSql;

            } else {
                $existingColumn = $existingColumns[$columnName];

                $existingColumnDef = $this->defineColumn($existingColumn);
                $columnDef = $this->defineColumn($column);
                $comment = "/* Existing: $existingColumnDef, New: $columnDef */\n";

                if ($existingColumnDef != $columnDef) {
                    // The existing & new column types do not match, so modify the column.
                    $alterSql[] = $comment . 'change `' . $columnName . '` ' . $this->defineColumn(val($columnName, $this->_columns));
                    // Check for a modification from an enum to an int.
                    if (strcasecmp($existingColumn->type, 'enum') == 0 && in_array(strtolower($column->type), $this->types('int'))) {
                        $sql = "update `$px{$this->_table}` set `$columnName` = case `$columnName`";
                        foreach ($existingColumn->enum as $index => $newValue) {
                            $oldValue = $index + 1;

                            if (!is_numeric($newValue))
                                continue;
                            $newValue = (int)$newValue;

                            $sql .= " when $oldValue then $newValue";
                        }
                        $sql .= " else `$columnName` end";
                        $description = "Update {$this->_table}.$columnName enum values to {$column->type}";
                        $addSql[$description] = $sql;

                    }
                }
            }
            $prevColumnName = $columnName;
        }

        if (count($alterSql) > 0) {
            if (!$this->query($alterSqlPrefix . implode(",\n", $alterSql))) {
                throw new Exception\Custom(t('Failed to alter the `%s` table.'), $this->_prefix . $this->_table);
            }
        }

        // 4. Update indexes.
        $indexSql = array();
        // Go through the indexes to add or modify.
        foreach ($indexes as $name => $sql) {
            if (isset($indexesDb[$name])) {
                if ($indexes[$name] != $indexesDb[$name]) {
                    if ($name == 'PRIMARY')
                        $indexSql[$name][] = $alterSqlPrefix . "drop primary key;\n";
                    else
                        $indexSql[$name][] = $alterSqlPrefix . 'drop index ' . $name . ";\n";
                    $indexSql[$name][] = $alterSqlPrefix . "add $sql;\n";
                }
                unset($indexesDb[$name]);
            } else {
                $indexSql[$name][] = $alterSqlPrefix . "add $sql;\n";
            }
        }
        // Go through the indexes to drop.
        if ($explicit) {
            foreach ($indexesDb as $name => $sql) {
                if ($name == 'PRIMARY')
                    $indexSql[$name][] = $alterSqlPrefix . "drop primary key;\n";
                else
                    $indexSql[$name][] = $alterSqlPrefix . 'drop index ' . $name . ";\n";
            }
        }

        // Modify all of the indexes.
        foreach ($indexSql as $name => $sqls) {
            foreach ($sqls as $sql) {
                if (!$this->query($sql))
                    throw new Exception\Custom(t('Error.ModifyIndex', 'Failed to add or modify the `%1$s` index in the `%2$s` table.'), array($name, $this->_table));
            }
        }

        // Run any additional Sql.
        foreach ($addSql as $description => $sql) {
            if (!$this->query($sql))
                throw new Exception\Custom("Error modifying table: {$description}.");
        }

        $this->reset();
        return true;
    }

    /**
     * Creates the table defined with $this->table() and $this->column().
     */
    protected function create()
    {
        $primaryKey = array();
        $uniqueKey = array();
        $fullTextKey = array();
        $allowFullText = true;
        $indexes = array();
        $keys = '';
        $sql = '';

        $forceEngine = c('database.forceStorageEngine');
        if ($forceEngine && !$this->_engine) {
            $this->_engine = $forceEngine;
            $allowFullText = $this->supportsFulltext();
        }

        foreach ($this->_columns as $columnName => $column) {
            if ($sql != '')
                $sql .= ',';

            $sql .= "\n" . $this->defineColumn($column);

            $columnKeyTypes = (array)$column->keyType;

            foreach ($columnKeyTypes as $columnKeyType) {
                $keyTypeParts = explode('.', $columnKeyType, 2);
                $columnKeyType = $keyTypeParts[0];
                $indexGroup = val(1, $keyTypeParts, '');

                if ($columnKeyType == 'primary' && !$column->autoIncrement) {
                    $primaryKey[] = $columnName;
                } elseif ($columnKeyType == 'key') {
                    $indexes['FK'][$indexGroup][] = $columnName;
                } elseif ($columnKeyType == 'index') {
                    $indexes['IX'][$indexGroup][] = $columnName;
                } elseif ($columnKeyType == 'unique') {
                    $uniqueKey[] = $columnName;
                } elseif ($columnKeyType == 'fulltext' && $allowFullText) {
                    $fullTextKey[] = $columnName;
                }
            }
        }
        // Build primary keys
        if (count($primaryKey) > 0) {
            $keys .= ",\nprimary key (`" . implode('`, `', $primaryKey) . "`)";
        }
        // Build unique keys.
        if (count($uniqueKey) > 0) {
            $keys .= ",\nunique index `" . $this->alphaNumeric('UX_' . $this->_table) . '` (`' . implode('`, `', $uniqueKey) . "`)";
        }
        // Build full text index.
        if (count($fullTextKey) > 0) {
            $keys .= ",\nfulltext index `" . $this->alphaNumeric('TX_' . $this->_table) . '` (`' . implode('`, `', $fullTextKey) . "`)";
        }
        // Build the rest of the keys.
        foreach ($indexes as $indexType => $indexGroups) {
            $createString = val($indexType, array('FK' => 'key', 'IX' => 'index'));
            foreach ($indexGroups as $indexGroup => $columnNames) {
                if (!$indexGroup) {
                    foreach ($columnNames as $columnName) {
                        $keys .= ",\n{$createString} `{$indexType}_{$this->_table}_{$columnName}` (`{$columnName}`)";
                    }
                } else {
                    $keys .= ",\n{$createString} `{$indexType}_{$this->_table}_{$indexGroup}` (`" . implode('`, `', $columnNames) . '`)';
                }
            }
        }

        $sql = 'create table `' . $this->_prefix . $this->_table . '` (' . $sql . $keys . "\n)";

        // Check to see if there are any fulltext columns, otherwise use innodb.
        if (!$this->_engine) {
            $hasFulltext = false;
            foreach ($this->_columns as $column) {
                $columnKeyTypes = (array)$column->keyType;
                array_map('strtolower', $columnKeyTypes);
                if (in_array('fulltext', $columnKeyTypes)) {
                    $hasFulltext = true;
                    break;
                }
            }
            if ($hasFulltext)
                $this->_engine = 'myisam';
            else
                $this->_engine = c('database.storageEngine', 'innodb');

            if (!$this->hasEngine($this->_engine)) {
                $this->_engine = 'myisam';
            }
        }

        if ($this->_engine)
            $sql .= ' engine=' . $this->_engine;

        if ($this->_encoding !== false && $this->_encoding != '')
            $sql .= ' default character set ' . $this->_encoding;

        // if (array_key_exists('Collate', $this->database->extendedProperties)) {
        //     $sql .= ' collate ' . $this->database->extendedProperties['Collate'];
        // }

        $sql .= ';';

        $result = $this->query($sql);
        $this->reset();

        return $result;
    }

    protected function alphaNumeric($string)
    {
        return preg_replace('/([^\w\d_-])/', '', $string);
    }

    protected function indexSql($columns, $keyType = false)
    {
        $result = array();
        $keys = array();
        $prefixes = array('key' => 'FK_', 'index' => 'IX_', 'unique' => 'UX_', 'fulltext' => 'TX_');
        $indexes = array();

        // Gather the names of the columns.
        foreach ($columns as $columnName => $column) {
            $columnKeyTypes = (array)$column->keyType;

            foreach ($columnKeyTypes as $columnKeyType) {
                $parts = explode('.', $columnKeyType, 2);
                $columnKeyType = $parts[0];
                $indexGroup = val(1, $parts, '');

                if (!$columnKeyType || ($keyType && $keyType != $columnKeyType))
                    continue;

                // Don't add a fulltext if we don't support.
                if ($columnKeyType == 'fulltext' && !$this->supportsFulltext())
                    continue;

                if ($columnKeyType == 'primary' && $column->autoIncrement)
                    continue;

                $indexes[$columnKeyType][$indexGroup][] = $columnName;
            }
        }

        // Make the multi-column keys into sql statements.
        foreach ($indexes as $columnKeyType => $indexGroups) {
            $createType = val($columnKeyType, array('index' => 'index', 'key' => 'key', 'unique' => 'unique index', 'fulltext' => 'fulltext index', 'primary' => 'primary key'));

            if ($columnKeyType == 'primary') {
                $result['PRIMARY'] = 'primary key (`' . implode('`, `', $indexGroups['']) . '`)';
            } else {
                foreach ($indexGroups as $indexGroup => $columnNames) {
                    $multi = (strlen($indexGroup) > 0 || in_array($columnKeyType, array('unique', 'fulltext')));

                    if ($multi) {
                        $indexName = "{$prefixes[$columnKeyType]}{$this->_table}" . ($indexGroup ? '_' . $indexGroup : '');

                        $result[$indexName] = "$createType $indexName (`" . implode('`, `', $columnNames) . '`)';
                    } else {
                        foreach ($columnNames as $columnName) {
                            $indexName = "{$prefixes[$columnKeyType]}{$this->_table}_$columnName";

                            $result[$indexName] = "$createType $indexName (`$columnName`)";
                        }
                    }
                }
            }
        }

        return $result;
    }

    protected function indexSqlDb()
    {
        // We don't want this to be captured so send it directly.
        $data = $this->_query('show indexes from ' . $this->_prefix . $this->_table);


        $result = array();
        foreach ($data as $row) {
            if (isset($result[$row->Key_name])) {
                $result[$row->Key_name] .= ', `' . $row->Column_name . '`';
            } else {
                switch (strtoupper(substr($row->Key_name, 0, 2))) {
                    case 'PR':
                        $type = 'primary key';
                        break;
                    case 'FK':
                        $type = 'key ' . $row->Key_name;
                        break;
                    case 'IX':
                        $type = 'index ' . $row->Key_name;
                        break;
                    case 'UX':
                        $type = 'unique index ' . $row->Key_name;
                        break;
                    case 'TX':
                        $type = 'fulltext index ' . $row->Key_name;
                        break;
                    default:
                        // Try and guess the index type.
                        if (strcasecmp($row->Index_type, 'fulltext') == 0)
                            $type = 'fulltext index ' . $row->Key_name;
                        elseif ($row->Non_unique)
                            $type = 'index ' . $row->Key_name;
                        else
                            $type = 'unique index ' . $row->Key_name;

                        break;
                }
                $column = val($row->Column_name, $this->_columns);

                if (!$column) continue;

                if ($type == 'primary key' && $column->autoIncrement)
                    continue;

                $result[$row->Key_name] = $type . ' (`' . $row->Column_name . '`';
            }
        }

        // Cap off the sql.
        foreach ($result as $name => $sql) {
            $result[$name] .= ')';
        }

        return $result;
    }

    protected function defineColumn($column)
    {
        if (!is_array($column->type) && !in_array($column->type, array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'char', 'varchar', 'varbinary', 'date', 'datetime', 'mediumtext', 'longtext', 'text', 'decimal', 'numeric', 'float', 'double', 'enum', 'timestamp', 'tinyblob', 'blob', 'mediumblob', 'longblob')))
            throw new Exception\Custom(t('The specified data type (%1$s) is not accepted for the MySQL database.'), $column->type);

        $return = '`' . $column->name . '` ' . $column->type;

        $lengthTypes = $this->types('length');
        if ($column->length != '' && in_array(strtolower($column->type), $lengthTypes)) {
            if ($column->precision != '')
                $return .= '(' . $column->length . ', ' . $column->precision . ')';
            else
                $return .= '(' . $column->length . ')';
        }
        if (property_exists($column, 'unsigned') && $column->unsigned) {
            $return .= ' unsigned';
        }

        if (is_array($column->enum))
            $return .= "('" . implode("','", $column->enum) . "')";

        if (!$column->allowNull)
            $return .= ' not null';

        if (!(is_null($column->default) || $column->default === '') && strcasecmp($column->type, 'timestamp') != 0)
            $return .= " default " . self::quotevalue($column->default);

        if ($column->autoIncrement)
            $return .= ' auto_increment primary key';

        return $return;
    }

    protected static function quoteValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        } else if (is_bool($value)) {
            return $value ? '1' : '0';
        } else {
            return "'" . str_replace("'", "''", $value) . "'";
        }
    }

    protected function supportsFulltext()
    {
        return strcasecmp($this->_engine, 'myisam') == 0;
    }
}