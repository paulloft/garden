<?php
namespace Garden\Db\Driver;
use \Garden\Exception as Exception;
use \Garden\Db\Database;
/**
 * MySQLi database connection.
 *
 * @package    Kohana/Database
 * @category   Drivers
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class MySQLi extends SQL {

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

        if (MySQLi::$_set_names === NULL)
        {
            // Determine if we can use mysqli_set_charset(), which is only
            // available on PHP 5.2.3+ when compiled against MySQL 5.0+
            MySQLi::$_set_names = ! function_exists('mysqli_set_charset');
        }

        // Extract the connection parameters, adding required variabels
        $hostname   = val('host', $this->_config, 'localhost');
        $database   = val('database', $this->_config);
        $username   = val('username', $this->_config, NULL);
        $password   = val('password', $this->_config, NULL);
        $socket     = val('socket', $this->_config);
        $port       = val('port', $this->_config, 3306);
        $ssl        = val('ssl', $this->_config, NULL);

        try
        {
            if(is_array($ssl))
            {
                $this->_connection = mysqli_init();
                $this->_connection->ssl_set(
                    val('client_key_path', $ssl),
                    val('client_cert_path', $ssl),
                    val('ca_cert_path', $ssl),
                    val('ca_dir_path', $ssl),
                    val('cipher', $ssl)
                );
                $this->_connection->real_connect($hostname, $username, $password, $database, $port, $socket, MYSQLI_CLIENT_SSL);
            }
            else
            {
                $this->_connection = new mysqli($hostname, $username, $password, $database, $port, $socket);
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

        if ( ! empty($this->_config['charset']))
        {
            // Set the character set
            $this->set_charset($this->_config['charset']);
        }

        if ( ! empty($this->_config['variables']))
        {
            // Set session variables
            $variables = array();

            foreach ($this->_config['variables'] as $var => $val)
            {
                $variables[] = 'SESSION '.$var.' = '.$this->quote($val);
            }

            $this->_connection->query('SET '.implode(', ', $variables));
        }
    }

    public function disconnect()
    {
        try
        {
            // Database is assumed disconnected
            $status = TRUE;

            if (is_resource($this->_connection))
            {
                if ($status = $this->_connection->close())
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

        if (MySQLi::$_set_names === TRUE)
        {
            // PHP is compiled against MySQL 4.x
            $status = (bool) $this->_connection->query('SET NAMES '.$this->quote($charset));
        }
        else
        {
            // PHP is compiled against MySQL 5.x
            $status = $this->_connection->set_charset($charset);
        }

        if ($status === FALSE)
        {
            throw new \Exception($this->_connection->error, $this->_connection->errno);
        }
    }

    public function query($type, $sql, $as_object = FALSE, array $params = NULL)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        // Execute the query
        if (($result = $this->_connection->query($sql)) === FALSE)
        {
            throw new Exception\Custom('%s [ %s ]', array($this->_connection->error, $sql), $this->_connection->errno);
        }

        // Set the last query
        $this->last_query = $sql;

        if ($type === Database::SELECT)
        {
            // Return an iterator of results
            return new MySQLi\Result($result, $sql, $as_object, $params);
        }
        elseif ($type === Database::INSERT)
        {
            // Return a list of insert id and rows created
            return array(
                $this->_connection->insert_id,
                $this->_connection->affected_rows,
            );
        }
        else
        {
            // Return the number of rows affected
            return $this->_connection->affected_rows;
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

        if ($mode AND ! $this->_connection->query("SET TRANSACTION ISOLATION LEVEL $mode"))
        {
            throw new \Exception($this->_connection->error, $this->_connection->errno);
        }

        return (bool) $this->_connection->query('START TRANSACTION');
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

        return (bool) $this->_connection->query('COMMIT');
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

        return (bool) $this->_connection->query('ROLLBACK');
    }

    public function escape($value)
    {
        // Make sure the database is connected
        $this->_connection or $this->connect();

        if (($value = $this->_connection->real_escape_string( (string) $value)) === FALSE)
        {
            throw new \Exception($this->_connection->error, $this->_connection->errno);
        }

        // SQL standard is to use single-quotes for all values
        return "'$value'";
    }

} // End MySQLi
