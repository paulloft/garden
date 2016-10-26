<?php 
namespace Garden\Db\Driver;
use \Garden\Exception as Exception;
use \Garden\Db\Database;
use \PDO as _PDO;
/**
 * PDO database connection.
 *
 * @package    Kohana/Database
 * @category   Drivers
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class PDO extends SQL {

    // PDO uses no quoting for identifiers
    protected $_identifier = '';

    public function __construct($name, array $config)
    {
        parent::__construct($name, $config);

        if (isset($this->_config['identifier']))
        {
            // Allow the identifier to be overloaded per-connection
            $this->_identifier = (string) $this->_config['identifier'];
        }
    }

    public function connect()
    {
        if ($this->_connection)
            return;

        // Extract the connection parameters, adding required variabels
        $host       = val('host', $this->_config, 'localhost');
        $type       = val('type', $this->_config, 'mysql');
        $database   = val('database', $this->_config);
        $username   = val('username', $this->_config, NULL);
        $password   = val('password', $this->_config, NULL);
        $persistent = val('persistent', $this->_config);

        $dsn = "$type:host=$host;dbname=$database";

        // Force PDO to use exceptions for all errors
        $options[_PDO::ATTR_ERRMODE] = _PDO::ERRMODE_EXCEPTION;

        if (!empty($persistent)) {
            // Make the connection persistent
            $options[_PDO::ATTR_PERSISTENT] = TRUE;
        }

        try {
            // Create a new PDO connection
            $this->_connection = new _PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        if ( ! empty($this->_config['charset'])) {
            // Set the character set
            $this->set_charset($this->_config['charset']);
        }
    }

    /**
     * Create or redefine a SQL aggregate function.
     *
     * [!!] Works only with SQLite
     *
     * @link http://php.net/manual/function.pdo-sqlitecreateaggregate
     *
     * @param   string      $name       Name of the SQL function to be created or redefined
     * @param   callback    $step       Called for each row of a result set
     * @param   callback    $final      Called after all rows of a result set have been processed
     * @param   integer     $arguments  Number of arguments that the SQL function takes
     *
     * @return  boolean
     */
    public function create_aggregate($name, $step, $final, $arguments = -1)
    {
        $this->_connection or $this->connect();

        return $this->_connection->sqliteCreateAggregate(
            $name, $step, $final, $arguments
        );
    }

    /**
     * Create or redefine a SQL function.
     *
     * [!!] Works only with SQLite
     *
     * @link http://php.net/manual/function.pdo-sqlitecreatefunction
     *
     * @param   string      $name       Name of the SQL function to be created or redefined
     * @param   callback    $callback   Callback which implements the SQL function
     * @param   integer     $arguments  Number of arguments that the SQL function takes
     *
     * @return  boolean
     */
    public function create_function($name, $callback, $arguments = -1)
    {
        $this->_connection or $this->connect();

        return $this->_connection->sqliteCreateFunction(
            $name, $callback, $arguments
        );
    }

    public function disconnect()
    {
        // Destroy the PDO object
        $this->_connection = NULL;

        return parent::disconnect();
    }

    public function set_charset($charset)
    {
        // Make sure the database is connected
        $this->_connection OR $this->connect();

        // This SQL-92 syntax is not supported by all drivers
        $this->_connection->exec('SET NAMES '.$this->quote($charset));
    }

    public function query($type, $sql, $as_object = FALSE, array $params = NULL)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        try  {
            $result = $this->_connection->query($sql);
        } catch (\Exception $e) {
            // Convert the exception in a database exception
            throw new Exception\Custom("%s \n[ %s ]", array($e->getMessage(), $sql), $e->getCode());
        }

        // Set the last query
        $this->last_query = $sql;

        if ($type === Database::SELECT) {
            // Convert the result into an array, as PDOStatement::rowCount is not reliable
            if ($as_object === FALSE) {
                $result->setFetchMode(_PDO::FETCH_ASSOC);
            } elseif (is_string($as_object)) {
                $result->setFetchMode(_PDO::FETCH_CLASS, $as_object, $params);
            } else {
                $result->setFetchMode(_PDO::FETCH_CLASS, 'stdClass');
            }

            $result = $result->fetchAll();

            // Return an iterator of results
            return new Database\Result\Cached($result, $sql, $as_object, $params);
        }
        elseif ($type === Database::INSERT) {
            // Return a list of insert id and rows created
            return array(
                $this->_connection->lastInsertId(),
                $result->rowCount(),
            );
        }
        else
        {
            // Return the number of rows affected
            return $result->rowCount();
        }
    }

    public function begin($mode = NULL)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return $this->_connection->beginTransaction();
    }

    public function commit()
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return $this->_connection->commit();
    }

    public function rollback()
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return $this->_connection->rollBack();
    }

    public function escape($value)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return $this->_connection->quote($value);
    }

} // End Database_PDO
