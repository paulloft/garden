<?php 
namespace Garden\Db\Driver;
use \Garden\Exception as Exception;
use \Garden\Db\Database;
/**
 * MySQL database connection.
 *
 * @package    Kohana/Database
 * @category   Drivers
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class MySQL extends SQL {

    // Database in use by each connection
    protected static $_current_databases = array();

    // Use SET NAMES to set the character set
    protected static $_set_names;

    // Identifier for this connection within the PHP driver
    protected $_connection_id;

    // MySQL uses a backtick for identifiers
    protected $_identifier = '`';

    public function connect()
    {
        if ($this->_connection)
            return;

        if (MySQL::$_set_names === NULL)
        {
            // Determine if we can use mysql_set_charset(), which is only
            // available on PHP 5.2.3+ when compiled against MySQL 5.0+
            MySQL::$_set_names = ! function_exists('mysql_set_charset');
        }


        // Extract the connection parameters, adding required variabels
        $hostname   = val('host', $this->_config, 'localhost');
        $database   = val('database', $this->_config);
        $username   = val('username', $this->_config, NULL);
        $password   = val('password', $this->_config, NULL);
        $persistent = val('persistent', $this->_config);

        try
        {
            if ($persistent)
            {
                // Create a persistent connection
                $this->_connection = mysql_pconnect($hostname, $username, $password);
            }
            else
            {
                // Create a connection and force it to be a new link
                $this->_connection = mysql_connect($hostname, $username, $password, TRUE);
            }
        }
        catch (\Exception $e)
        {
            // No connection exists
            $this->_connection = NULL;

            throw new \Exception($e->getMessage(), $e->getCode());
        }

        // \xFF is a better delimiter, but the PHP driver uses underscore
        $this->_connection_id = sha1($hostname.'_'.$username.'_'.$password);

        $this->_select_db($database);

        if ( ! empty($this->_config['charset']))
        {
            // Set the character set
            $this->set_charset($this->_config['charset']);
        }

        $vars = val('variables', $this->_config);

        if (!empty($vars))
        {
            // Set session variables
            $variables = array();

            foreach ($vars as $var => $val)
            {
                $variables[] = 'SESSION '.$var.' = '.$this->quote($val);
            }

            mysql_query('SET '.implode(', ', $variables), $this->_connection);
        }
    }

    /**
     * Select the database
     *
     * @param   string  $database Database
     * @return  void
     */
    protected function _select_db($database)
    {
        if ( ! mysql_select_db($database, $this->_connection))
        {
            // Unable to select database
            throw new \Exception(mysql_error($this->_connection), mysql_errno($this->_connection));
        }

       MySQL::$_current_databases[$this->_connection_id] = $database;
    }

    public function disconnect()
    {
        try
        {
            // Database is assumed disconnected
            $status = TRUE;

            if (is_resource($this->_connection))
            {
                if ($status = mysql_close($this->_connection))
                {
                    // Clear the connection
                    $this->_connection = NULL;

                    // Clear the instance
                    parent::disconnect();
                }
            }
        }
        catch (\Exception $e)
        {
            // Database is probably not disconnected
            $status = ! is_resource($this->_connection);
        }

        return $status;
    }

    public function set_charset($charset)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        if (MySQL::$_set_names === TRUE)
        {
            // PHP is compiled against MySQL 4.x
            $status = (bool) mysql_query('SET NAMES '.$this->quote($charset), $this->_connection);
        }
        else
        {
            // PHP is compiled against MySQL 5.x
            $status = mysql_set_charset($charset, $this->_connection);
        }

        if ($status === FALSE)
        {
            throw new \Exception(mysql_error($this->_connection), mysql_errno($this->_connection));
        }
    }

    public function query($type, $sql, $as_object = FALSE, array $params = NULL)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        if ( ! empty($this->_config['persistent']) AND $this->_config['database'] !== MySQL::$_current_databases[$this->_connection_id])
        {
            // Select database on persistent connections
            $this->_select_db($this->_config['database']);
        }

        // Execute the query
        if (($result = mysql_query($sql, $this->_connection)) === FALSE)
        {
            throw new Exception\Custom('%s [ %s ]', array(mysql_error($this->_connection), $sql), mysql_errno($this->_connection));
        }

        // Set the last query
        $this->last_query = $sql;

        if ($type === Database::SELECT)
        {
            // Return an iterator of results
            return new MySQL\Result($result, $sql, $as_object, $params);
        }
        elseif ($type === Database::INSERT)
        {
            // Return a list of insert id and rows created
            return array(
                mysql_insert_id($this->_connection),
                mysql_affected_rows($this->_connection),
            );
        }
        else
        {
            // Return the number of rows affected
            return mysql_affected_rows($this->_connection);
        }
    }

    /**
     * Start a SQL transaction
     *
     * @link http://dev.mysql.com/doc/refman/5.0/en/set-transaction.html
     *
     * @param string $mode  Isolation level
     * @return boolean
     */
    public function begin($mode = NULL)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        if ($mode AND ! mysql_query("SET TRANSACTION ISOLATION LEVEL $mode", $this->_connection))
        {
            throw new \Exception(mysql_error($this->_connection), mysql_errno($this->_connection));               
        }

        return (bool) mysql_query('START TRANSACTION', $this->_connection);
    }

    /**
     * Commit a SQL transaction
     *
     * @return boolean
     */
    public function commit()
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return (bool) mysql_query('COMMIT', $this->_connection);
    }

    /**
     * Rollback a SQL transaction
     *
     * @return boolean
     */
    public function rollback()
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        return (bool) mysql_query('ROLLBACK', $this->_connection);
    }

    public function escape($value)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        if (($value = mysql_real_escape_string( (string) $value, $this->_connection)) === FALSE)
        {
            throw new \Exception(mysql_error($this->_connection), mysql_errno($this->_connection));
        }

        // SQL standard is to use single-quotes for all values
        return "'$value'";
    }

} // End Database_MySQL
