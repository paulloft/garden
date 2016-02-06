<?php
namespace Garden;

/**
 * Database manager
 * 
 * The Database object contains connection and engine information for a single database.
 * It also allows a database to execute string sql statements against that database.
 * 
**/
class Database extends Plugin
{
    protected $_sql = NULL;

    /** 
     * @param mixed $config The configuration settings for this object.
     * @see Database::init()
     */
    public function __construct($config = NULL) {
        $this->className = get_class($this);
        $this->init($config);
    }

    /**
    * Get the database driver class for the database.
    * @return Gdn_SQLDriver The database driver class associated with this database.
    */
    public function sql() {
        return $this->_sql;
    }

    protected function init($config = NULL) {
        if(is_null($config)) {
            $config = c('Database');
        }
        $defaultConfig = c('Database');
        $config = array_merge($defaultConfig, $config);

        $prefix = val('tablePrefix', $config);

        $db = Db\Db::create($config);
        $db->setPx($prefix);


        $this->_sql = $db;
    }
}