<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden;


use Garden\Exception\ValidationException;

class Schema {
    /// Properties ///
    protected $schema = [];

    protected static $types = [
//        '@' => 'file',
        'a' => 'array',
        'o' => 'object',
        '=' => 'base64',
        'i' => 'integer',
        's' => 'string',
        'f' => 'float',
        'b' => 'boolean',
        'ts' => 'timestamp'
    ];

    /**
     * @var array An array of callbacks that will custom validate the schema.
     */
    protected $validators = [];

    /// Methods ///

    public function __construct($schema = []) {
        $this->schema = static::parse($schema);
    }

    /**
     * Create a new schema and return it.
     *
     * @param array $schema The schema array.
     * @return Schema Returns the newly created and parsed schema.
     */
    public static function create($schema = []) {
        $new = new Schema($schema);
        return $new;
    }

    /**
     * Parse a schema in short form into a full schema array.
     *
     * @param array $arr The array to parse into a schema.
     * @return array The full schema array.
     * @throws \InvalidArgumentException Throws an exception when an item in the schema is invalid.
     */
    public static function parse(array $arr) {
        $result = [];

        foreach ($arr as $key => $value) {
            if (is_int($key)) {
                if (is_string($value)) {
                    // This is a short param value.
                    list($name, $param) = static::parseShortParam($value);
                    $result[$name] = $param;
                } else {
                    throw new \InvalidArgumentException("Schema at position $key is not a valid param.", 422);
                }
            } else {
                // The parameter is defined in the key.
                list($name, $param) = static::parseShortParam($key, $value);

                if (is_array($value)) {
                    // The value describes a bit more about the schema.
                    switch ($param['type']) {
                        case 'array':
                            if (isset($value['items'])) {
                                // The value includes array schema information.
                                $param = array_replace($param, $value);
                            } else {
                                // The value is a schema of items.
                                $param['items'] = $value;
                            }
                            break;
                        case 'object':
                            // The value is a schema of the object.
                            $param['properties'] = static::parse($value);
                            break;
                        default:
                            $param = array_replace($param, $value);
                            break;
                    }
                } elseif (is_string($value)) {
                    if ($param['type'] === 'array') {
                        // Check to see if the value is the item type in the array.
                        if (isset(self::$types[$value])) {
                            $arrType = self::$types[$value];
                        } elseif (($index = array_search($value, self::$types)) !== false) {
                            $arrType = self::$types[$value];
                        }

                        if (isset($arrType)) {
                            $param['items'] = ['type' => $arrType];
                        } else {
                            $param['description'] = $value;
                        }
                    } else {
                        // The value is the schema description.
                        $param['description'] = $value;
                    }
                }

                $result[$name] = $param;
            }
        }

        return $result;
    }

    /**
     * Parse a short parameter string into a full array parameter.
     *
     * @param $str The short parameter string to parse.
     * @param array $other An array of other information that might help resolve ambiguity.
     * @return array Returns an array in the form [name, [param]].
     * @throws \InvalidArgumentException Throws an exception if the short param is not in the correct format.
     */
    public static function parseShortParam($str, $other = []) {
        // Is the parameter optional?
        if (str_ends($str, '?')) {
            $required = false;
            $str = substr($str, 0, -1);
        } else {
            $required = true;
        }

        // Check for a type.
        $parts = explode(':', $str);

        if (count($parts) === 1) {
            if (isset($other['type'])) {
                $type = $other['type'];
            } else {
                $type = 'string';
            }
            $name = $parts[0];
        } else {
            $name = $parts[1];

            if (isset(self::$types[$parts[0]])) {
                $type = self::$types[$parts[0]];
            } else {
                throw new \InvalidArgumentException("Invalid type {$parts[1]} for field $name.", 500);
            }
        }

        $result = [$name, ['type' => $type, 'required' => $required]];

        return $result;
    }

    /**
     * Add a custom validator to to validate the schema.
     *
     * @param callable $callback The callback to validate with.
     * @param string $field The name of the field to validate, if any.
     * @return Schema Returns `$this` for fluent calls.
     */
    public function addValidator(callable $callback, $field = '*') {
        $this->validators[$field][] = $callback;
        return $this;
    }


    /**
     * Require one of a given set of fields in the schema.
     *
     * @param array $fields The field names to require.
     * @param int $count The count of required items.
     * @return Schema Returns `$this` for fluent calls.
     */
    public function requireOneOf(array $fields, $count = 1) {
        return $this->addValidator(function ($data, Validation $validation) use ($fields, $count) {
            $hasCount = 0;

            foreach ($fields as $name) {
                if (isset($data[$name]) && $data[$name]) {
                    $hasCount++;
                }

                if ($hasCount >= $count) {
                    return true;
                }
            }

            if ($count === 1) {
                $message = sprintft('One of %s are required.', implode(', ', $fields));
            } else {
                $message = sprintft('%1$s of %2$s are required.', $count, implode(', ', $fields));
            }

            $validation->addError('missing_field', $fields, [
                'message' => $message
            ]);
        });
    }

    /**
     * Validate data against the schema.
     *
     * @param array &$data The data to validate.
     * @param Validation $validation This argument will be filled with the validation result.
     * @return bool Returns true if the data is valid, false otherwise.
     * @throws ValidationException Throws an exception when the data does not validate against the schema.
     */
    public function validate(array &$data, Validation &$validation = null) {
        if (!$this->isValid($data, $validation)) {
            throw new ValidationException($validation);
        }
        return $this;
    }

    /**
     * Validate data against the schema and return the result.
     *
     * @param array &$data The data to validate.
     * @param Validation $validation This argument will be filled with the validation result.
     * @return bool Returns true if the data is valid. False otherwise.
     */
    public function isValid(array &$data, Validation &$validation = null) {
        if ($validation === null) {
            $validation = new Validation();
        }

        // Validate the global validators first.
        if (isset($this->validators['*'])) {
            foreach ($this->validators['*'] as $callback) {
                call_user_func($callback, $data, $validation);
            }
        }

        // Loop through the schema fields and validate each one.
        foreach ($this->schema as $field => $params) {
            if (isset($data[$field])) {
                $this->validateField($data[$field], $field, $params, $validation);
            } elseif (val('required', $params)) {
                $validation->addError('missing_field', $field);
            }
        }

        return $validation->isValid();
    }

    /**
     * Validate a field.
     *
     * @param mixed &$value The value to validate.
     * @param string $field The name of the field to validate.
     * @param array $params Parameters on the field.
     * @param Validation $validation A validation object to add errors to.
     * @return bool Returns true if the field is valid, false otherwise.
     * @throws \InvalidArgumentException Throws an exception when there is something wrong in the {@link $params}.
     */
    public function validateField(&$value, $field, $params, Validation $validation) {
        $type = $params['type'];
        $required = val('required', $params, false);
        $valid = true;

        // Check required first.
        if ($value === '' || $value === null) {
            if (!$required) {
                if (!($type === 'boolean' && $value === false)) {
                    $value = null;
                }
                return true;
            }

            switch ($type) {
                case 'boolean':
                    $value = false;
                    return true;
                case 'string':
                    if (val('minLength', $params, 1) == 0) {
                        $value = '';
                        return true;
                    }
            }
            $validation->addError('missing_field', $field);
            return false;
        }

        // Validate the field's type.
        $validType = true;
        switch ($type) {
            case 'boolean':
                if (is_bool($value)) {
                    $validType = true;
                } else {
                    $bools = ['0' => false, 'false' => false, '1' => true, 'true' => true];
                    if (isset($bools[$value])) {
                        $value = $bools[$value];
                        $validType = true;
                    } else {
                        $validType = false;
                    }
                }
                break;
            case 'integer':
                if (is_int($value)) {
                    $validType = true;
                } elseif (is_numeric($value)) {
                    $value = (int)$value;
                    $validType = true;
                } else {
                    $validType = false;
                }
                break;
            case 'float':
                if (is_float($value)) {
                    $validType = true;
                } elseif (is_numeric($value)) {
                    $value = (float)$value;
                    $validType = true;
                } else {
                    $validType = false;
                }
                break;
            case 'string':
                if (is_string($value)) {
                    $validType = true;
                } elseif (is_numeric($value)) {
                    $value = (string)$value;
                    $validType = true;
                } else {
                    $validType = false;
                }
                break;
            case 'timestamp':
                if (is_numeric($value)) {
                    $value = (int)$value;
                    $validType = true;
                } elseif (is_string($value) && $ts = strtotime($value)) {
                    $value = $ts;
                } else {
                    $validType = false;
                }
                break;
            case 'base64':
                if (!is_string($value)
                    || !preg_match('`^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$`', $value)) {

                    $validType = false;
                }
                break;
            case 'array':
                if (!is_array($value) || !isset($value[0])) {
                    $validType = false;
                }
                break;
            case 'object':
                if (!is_array($value) || isset($value[0])) {
                    $validType = false;
                }
                break;
            default:
                throw new \InvalidArgumentException("Unrecognized type $type.", 422);
                break;
        }
        if (!$validType) {
            $valid = false;
            $validation->addError(
                'invalid_type',
                $field,
                ['message' => sprintft('%1$s is not a valid %2$s.', $field, $type)]
            );
        }

        return $valid;
    }
}
