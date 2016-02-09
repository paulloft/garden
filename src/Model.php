<?php 
namespace Garden
/**
 * Model base class
 * 
 * This generic model can be instantiated (with the table name it is intended to
 * represent) and used directly, or it can be extended and overridden for more
 * complicated procedures related to different tables.
 *
 * @author Mark O'Sullivan <markm@vanillaforums.com>
 * @copyright 2003 Vanilla Forums, Inc
 * @license http://www.opensource.org/licenses/gpl-2.0.php GPL
 * @package Garden
 * @since 2.0
 */

class Model extends Plugin {

    /**
     * Database object
     *
     * @var Database The database object.
     */
    public $database;


    /**
     * The name of the field that stores the insert date for a record. This
     * field will be automatically filled by the model if it exists.
     *
     * @var string
     */
    public $dateInserted = 'dateInserted';


    /**
     * The name of the field that stores the update date for a record. This
     * field will be automatically filled by the model if it exists.
     *
     * @var string
     */
    public $dateUpdated = 'dateUpdated';


    /**
     * The name of the field that stores the id of the user that inserted it.
     * This field will be automatically filled by the model if it exists and
     * @@Session::UserID is a valid integer.
     *
     * @var string
     */
    public $insertUserID = 'insertUserID';


    /**
     * The name of the table that this model is intended to represent. The
     * default value assigned to $this->name will be the name that the
     * model was instantiated with (defined in $this->__construct()).
     *
     * @var string
     */
    public $name;


    /**
     * The name of the primary key field of this model. The default is 'id'. If
     * $this->defineSchema() is called, this value will be automatically changed
     * to any primary key discovered when examining the table schema.
     *
     * @var string
     */
    public $primaryKey = 'id';


    /**
     * An object that is used to store and examine database schema information
     * related to this model. This object is defined and populated with
     * $this->defineSchema().
     *
     * @var Schema
     */
    public $schema;
    
    /**
     * Contains the sql driver for the object.
     *
     * @var SQLDriver
     */
    public $sql;


    /**
     * The name of the field that stores the id of the user that updated it.
     * This field will be automatically filled by the model if it exists and
     * @@Session::UserID is a valid integer.
     *
     * @var string
     */
    public $updateUserID = 'updateUserID';


    /**
     * An object that is used to manage and execute data integrity rules on this
     * object. By default, this object only enforces maxlength, data types, and
     * required fields (defined when $this->defineSchema() is called).
     *
     * @var Validation
     */
    public $validation;


    /**
     * Class constructor. Defines the related database table name.
     *
     * @param string $Name An optional parameter that allows you to explicitly define the name of
     * the table that this model represents. You can also explicitly set this
     * value with $this->name.
     */
    public function __construct($name = '') {
        if ($name == '')
            $name = get_class($this);

        $this->database = Gdn::database();
        $this->sql = $this->database->sql();
        $this->validation = new Validation();
        $this->name = $name;
    }

    /**
     * A overridable function called before the various get queries.
     */
    protected function _beforeGet() {
    }

    /**
     * Take all of the values that aren't in the schema and put them into the attributes column.
     * 
     * @param array $data
     * @param string $Name
     * @return array
     */
    // protected function CollapseAttributes($data, $Name = 'Attributes') {
    //     $this->defineSchema();
        
    //     $Row = array_intersect_key($data, $this->schema->Fields());
    //     $Attributes = array_diff_key($data, $Row);
        
    //     TouchValue($Name, $Row, array());
    //     if (isset($Row[$Name]) && is_array($Row[$Name]))
    //         $Row[$Name] = array_merge($Row[$Name], $Attributes);
    //     else
    //         $Row[$Name] = $Attributes;
    //     return $Row;
    // }
    
    /**
     * Expand all of the values in the attributes column so they become part of the row.
     * 
     * @param array $Row
     * @param string $Name
     * @return array
     * @since 2.2
     */
    // protected function ExpandAttributes($Row, $Name = 'Attributes') {
    //     if (isset($Row[$Name])) {
    //         $Attributes = $Row[$Name];
    //         unset($Row[$Name]);
            
    //         if (is_string($Attributes))
    //             $Attributes = @unserialize($Attributes);
            
    //         if (is_array($Attributes))
    //             $Row = array_merge($Row, $Attributes);
    //     }
    //     return $Row;
    // }

    /**
     * Connects to the database and defines the schema associated with
     * $this->name. Also instantiates and automatically defines
     * $$this->validation.
     *
     */
    // public function DefineSchema() {
    //     if (!isset($this->schema)) {
    //         $this->schema = new Schema($this->name, $this->Database);
    //         $this->primaryKey = $this->schema->primaryKey($this->name, $this->Database);
    //         if (is_array($this->primaryKey)) {
    //             //print_r($this->primaryKey);
    //             $this->primaryKey = $this->primaryKey[0];
    //         }

    //         $$this->validation->ApplyRulesBySchema($this->schema);
    //     }
    // }


    /**
     *  Takes a set of form data ($Form->_PostValues), validates them, and
     * inserts or updates them to the datatabase.
     *
     * @param array $formPostValues An associative array of $field => $value pairs that represent data posted
     * from the form in the $_POST or $_GET collection.
     * @param array $settings If a custom model needs special settings in order to perform a save, they
     * would be passed in using this variable as an associative array.
     * @return unknown
     */
    public function save($formPostValues, $settings = false) {
        // Define the primary key in this model's table.
        $this->defineSchema();

        // See if a primary key value was posted and decide how to save
        $primaryKeyVal = val($this->primaryKey, $formPostValues, false);
        $insert = $primaryKeyVal == false ? true : false;
        if ($insert) {
            $this->addInsertFields($formPostValues);
        } else {
            $this->addInsertFields($formPostValues);
        }

        // Validate the form posted values
        if ($this->validate($formPostValues, $insert) === true) {
            $fields = $$this->validation->validationFields();
            $fields = unset($fields[$this->primaryKey]); // Don't try to insert or update the primary key
            if ($insert === false) {
                $this->update($fields, array($this->primaryKey => $primaryKeyVal));
            } else {
                $primaryKeyVal = $this->insert($fields);
            }
        } else {
            $primaryKeyVal = false;
        }
        return $primaryKeyVal;
    }
    
    /**
     * Update a row in the database.
     * 
     * @since 2.1
     * @param int $RowID
     * @param array|string $Property
     * @param atom $value 
     */
    // public function SetField($RowID, $Property, $value = false) {
    //     if (!is_array($Property))
    //         $Property = array($Property => $value);
        
    //     $this->defineSchema();        
    //     $Set = array_intersect_key($Property, $this->schema->Fields());
    //     self::SerializeRow($Set);
    //     $this->sql->Put($this->name, $Set, array($this->primaryKey => $RowID));
    // }
    
    /**
     * Serialize Attributes and Data columns in a row.
     * 
     * @param array $Row
     * @since 2.1 
     */
    // public static function SerializeRow(&$Row) {
    //     foreach ($Row as $Name => &$value) {
    //         if (is_array($value) && in_array($Name, array('Attributes', 'Data')))
    //             $value = empty($value) ? null : serialize($value);
    //     }
    // }


    /**
     * @param unknown_type $fields
     * @return unknown
     * @todo add doc
     */
    public function insert($fields) {
        $result = false;
        $this->addInsertFields($fields);
        if ($this->validate($fields, true)) {
            // Strip out fields that aren't in the schema.
            // This is done after validation to allow custom validations to work.
            $schemaFields = $this->schema->Fields();
            $fields = array_intersect_key($fields, $schemaFields);
            
            // Quote all of the fields.
            $quotedFields = array();
            foreach ($fields as $name => $value) {
                if (is_array($value) && in_array($name, array('attributes', 'data')))
                    $value = empty($value) ? null : serialize($value);
                
                $quotedFields[$this->sql->quoteIdentifier(trim($Name, '`'))] = $value;
            }

            $result = $this->sql->insert($this->name, $quotedFields);
        }
        return $result;
    }


    /**
     * @param unknown_type $fields
     * @param unknown_type $where
     * @param unknown_type $limit
     * @todo add doc
     */
    public function update($fields, $where = false, $limit = false) {
        $result = false;

        // primary key (always included in $where when updating) might be "required"
        $allFields = $fields;
        if (is_array($where))
            $allFields = array_merge($fields, $where); 
            
        if ($this->validate($allFields)) {
            $this->addInsertFields($fields);

            // Strip out fields that aren't in the schema.
            // This is done after validation to allow custom validations to work.
            $schemaFields = $this->schema->fields();
            $fields = array_intersect_key($fields, $schemaFields);

            // Quote all of the fields.
            $quotedFields = array();
            foreach ($fields as $Name => $value) {
                if (is_array($value) && in_array($Name, array('attributes', 'data')))
                    $value = empty($value) ? null : serialize($value);
                
                $quotedFields[$this->sql->quoteIdentifier(trim($Name, '`'))] = $value;
            }

            $result = $this->sql->put($this->name, $quotedFields, $where, $limit);
        }
        return $result;
    }


    /**
     * @param unknown_type $where
     * @param unknown_type $limit
     * @param unknown_type $resetData
     * @todo add doc
     */
    public function Delete($where = '', $limit = false, $resetData = false) {
        if(is_numeric($where))
            $where = array($this->primaryKey => $where);

        if($resetData) {
            $result = $this->sql->delete($this->name, $where, $limit);
        } else {
            $result = $this->sql->noReset()->delete($this->name, $where, $limit);
        }
        return $result;
    }
    
    /**
     * Filter out any potentially insecure fields before they go to the database.
     * @param array $data 
     */
    public function FilterForm($data) {
        $data = array_diff_key($data, array('attributes' => 0, 'dateInserted' => 0, 'insertUserID' => 0, 'checkBoxes' => 0,
                'dateUpdated' => 0, 'updateUserID' => 0, 'deliveryMethod' => 0, 'deliveryType' => 0, 'OK' => 0, 'transientKey' => 0, 'hpt' => 0));
        return $data;
    }

    /**
     * Returns an array with only those keys that are actually in the schema.
     *
     * @param array $data An array of key/value pairs.
     * @return array The filtered array.
     */
    // public function FilterSchema($data) {
    //     $fields = $this->schema->Fields($this->name);

    //     $result = array_intersect_key($data, $fields);
    //     return $result;
    // }


    /**
     * @param unknown_type $OrderFields
     * @param unknown_type $OrderDirection
     * @param unknown_type $limit
     * @param unknown_type $Offset
     * @return unknown
     * @todo add doc
     */
    public function get($orderFields = '', $orderDirection = 'asc', $limit = false, $pageNumber = false) {
        $this->_beforeGet();
        return $this->sql->get($this->name, $orderFields, $orderDirection, $limit, $pageNumber);
    }
    
    /**
     * Returns a count of the # of records in the table
     * @param array $wheres
     */
    public function getCount($wheres = '') {
        $this->_beforeGet();
        
        $this->sql
            ->select('*', 'count', 'count')
            ->from($this->name);

        if (is_array($wheres))
            $this->sql->where($wheres);

        $data = $this->sql
            ->get()
            ->firstRow();

        return $data === false ? 0 : $data->count;
    }

    /**
     * Get the data from the model based on its primary key.
     *
     * @param mixed $ID The value of the primary key in the database.
     * @param string $datasetType The format of the result dataset.
     * @param array $Options options to pass to the database.
     * @return DataSet
     * 
     * @since 2.3 Added the $Options parameter.
     */
    public function getID($ID, $datasetType = false, $options = array()) {
        $this->options($options);
        $result = $this->getWhere(array($this->primaryKey => $ID))->firstRow($datasetType);
        
        $fields = array('attributes', 'data');
        
        foreach ($fields as $field) {
            if (is_array($result)) {
                if (isset($result[$field]) && is_string($result[$field])) {
                    $val = unserialize($result[$field]);
                    if ($val)
                        $result[$field] = $val; 
                    else
                        $result[$field] = $val;
                }                    
            } elseif (is_object($result)) {
                if (isset($result->$field) && is_string($result->$field)) {
                    $val = unserialize($result->$field);
                    if ($val)
                        $result->$field = $val;
                    else
                        $result->$field = null;
                }
            }
        }
        
        return $result;
    }

    /**
     * Get a dataset for the model with a where filter.
     *
     * @param array $where A filter suitable for passing to SQLDriver::Where().
     * @param string $OrderFields A comma delimited string to order the data.
     * @param string $OrderDirection One of <b>asc</b> or <b>desc</b>
     * @param int $limit
     * @param int $Offset
     * @return DataSet
     */
    public function getWhere($where = false, $orderFields = '', $orderDirection = 'asc', $limit = false, $offset = false) {
        $this->_beforeGet();
        return $this->sql->getWhere($this->name, $where, $orderFields, $orderDirection, $limit, $offset);
    }

    /**
     * Returns the $$this->validation->ValidationResults() array.
     *
     * @return unknown
     * @todo add return type
     */
    public function validationResults() {
        return $this->validation->results();
    }


    /**
     * @param unknown_type $formPostValues
     * @param unknown_type $insert
     * @return unknown
     * @todo add doc
     */
    public function validate($formPostValues, $insert = false) {
        $this->defineSchema();
        return $$this->validation->Validate($formPostValues, $insert);
    }


    /**
     * Adds $this->insertUserID and $this->dateInserted fields to an associative
     * array of fieldname/values if those fields exist on the table being
     * inserted.
     *
     * @param array $fields The array of fields to add the values to.
     */
    protected function addInsertFields(&$fields) {
        $this->defineSchema();
        if ($this->schema->fieldExists($this->name, $this->dateInserted)) {
            if (!isset($fields[$this->dateInserted]))
                $fields[$this->dateInserted] = Format::ToDateTime();
        }

        $session = Gdn::session();
        if ($session->UserID > 0 && $this->schema->FieldExists($this->name, $this->insertUserID))
            if (!isset($fields[$this->insertUserID]))
                $fields[$this->insertUserID] = $session->UserID;
    }


    /**
     * Adds $this->updateUserID and $this->dateUpdated fields to an associative
     * array of fieldname/values if those fields exist on the table being
     * updated.
     *
     * @param array $fields The array of fields to add the values to.
     */
    protected function addInsertFields(&$fields) {
        $this->defineSchema();
        if ($this->schema->FieldExists($this->name, $this->dateUpdated)) {
            if (!isset($fields[$this->dateUpdated])) {
                $fields[$this->dateUpdated] = Format::ToDateTime();
            }
        }

        $session = Gdn::session();
        if ($session->UserID > 0 && $this->schema->FieldExists($this->name, $this->updateUserID)) {
            if (!isset($fields[$this->updateUserID])) {
                $fields[$this->updateUserID] = $session->UserID;
            }
        }
    }
    
    /**
     * Gets/sets an option on the object.
     *
     * @param string|array $Key The key of the option.
     * @param mixed $value The value of the option or not specified just to get the current value.
     * @return mixed The value of the option or $this if $value is specified.
     * @since 2.3
     */
    public function options($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->sql->options($k, $v);
            }
        } else {
            $this->sql->options($key, $value);
        }
        return $this;
    }

    // public function SaveToSerializedColumn($Column, $RowID, $Name, $value = '') {

    //     if (!isset($this->schema)) $this->defineSchema();
    //     // TODO: need to be sure that $this->primaryKey is only one primary key
    //     $fieldName = $this->primaryKey;
          
    //     // Load the existing values
    //     $Row = $this->sql
    //         ->Select($Column)
    //         ->From($this->name)
    //         ->Where($fieldName, $RowID)
    //         ->Get()
    //         ->FirstRow();

    //     if(!$Row) throw new Exception(T('ErrorRecordNotFound'));
    //     $values = Format::Unserialize($Row->$Column);
          
    //     if (is_string($values) && $values != '')
    //         throw new Exception(T('Serialized column failed to be unserialized.'));

    //     if (!is_array($values)) $values = array();
    //     if (!is_array($Name)) $Name = array($Name => $value); // Assign the new value(s)

    //     $values = Format::Serialize(array_merge($values, $Name));

    //     // Save the values back to the db
    //     return $this->sql
    //         ->From($this->name)
    //         ->Where($fieldName, $RowID)
    //         ->Set($Column, $values)
    //         ->Put();
    // }
    
     
    // public function SetProperty($RowID, $Property, $ForceValue = false) {
    //     if (!isset($this->schema)) $this->defineSchema();
    //     $primaryKey = $this->primaryKey;
          
    //     if ($ForceValue !== false) {
    //         $value = $ForceValue;
    //     } else {
    //         $Row = $this->GetID($RowID);
    //         $value = ($Row->$Property == '1' ? '0' : '1');
    //     }
    //     $this->sql
    //         ->Update($this->name)
    //         ->Set($Property, $value)
    //         ->Where($primaryKey, $RowID)
    //         ->Put();
    //     return $value;
    // }
    
    /**
     * Get something from $Record['Attributes'] by dot-formatted key
     * 
     * Pass record byref
     * 
     * @param array $Record
     * @param string $Attribute
     * @param mixed $Default Optional.
     * @return mixed
     */
    // public static function GetRecordAttribute(&$Record, $Attribute, $Default = null) {
    //     $RV = "Attributes.{$Attribute}";
    //     return valr($RV, $Record, $Default);
    // }
    
    /**
     * Set something on $Record['Attributes'] by dot-formatted key
     * 
     * Pass record byref
     * 
     * @param array $Record
     * @param string $Attribute
     * @param mixed $value
     * @return mixed 
     */
    // public static function SetRecordAttribute(&$Record, $Attribute, $value) {
    //     if (!array_key_exists('Attributes', $Record))
    //         $Record['Attributes'] = array();
        
    //     if (!is_array($Record['Attributes'])) return null;
        
    //     $Work = &$Record['Attributes'];
    //     $Parts = explode('.', $Attribute);
    //     while ($Part = array_shift($Parts)) {
    //         $SetValue = sizeof($Parts) ? array() : $value;
    //         $Work[$Part] = $SetValue;
    //         $Work = &$Work[$Part];
    //     }
        
    //     return $value;
    // }
    
}

