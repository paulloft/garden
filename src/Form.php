<?php
namespace Garden;

/**
 * @todo Доделать множественные инпуты с именами вида name="field[]"
 */
class Form
{
    /**
     * @var string form method
     */
    public $method = 'post';

    /**
     * @var string default css class for input
     */
    public $inputClass = 'form-control';

    /**
     * @var \Garden\Model
     */
    protected $model;
    protected $validation;
    protected $data;
    protected $formValues;

    private $hiddenInputs = [];
    private $inputs;

    /**
     * Form constructor.
     * @param string $tablename table name for form model
     */
    function __construct($tablename = false)
    {
        if ($tablename) {
            $model = new Model($tablename);
            $this->setModel($model);
        }
    }

    /**
     * set form model
     * @param object $model table model
     * @param array|object $dataset
     */
    public function setModel($model, $dataset = false)
    {
        $this->model = $model;

        if ($dataset !== false)
            $this->setdata($dataset);
    }

    /**
     * set form data
     * @param array|object $data
     */
    public function setData($data)
    {
        if (is_object($data)) {
            if ($data instanceof Db\Database\Result) {
                $this->data = $data->current();
            } elseif ($data instanceof \StdClass) {
                $this->data = (array)$data;
            }
        } elseif (is_array($data)) {
            $this->data = $data;
        }

        if ($this->model) {
            $id = val($this->model->primaryKey, $this->data, null);
            $this->addHidden($this->model->primaryKey, $id, true);
        }

    }

    /**
     * return form validation class
     * @return Validation
     */
    public function validation()
    {
        if (!$this->validation) {
            $this->validation = new Validation($this->model);
        }

        return $this->validation;
    }

    /**
     * check the form is submitted
     * @return bool
     */
    public function submitted()
    {
        $method = strtoupper($this->method);
        switch ($method) {
            case Request::METHOD_GET:
                return count(Gdn::request()->getQuery()) > 0 || (is_array($this->getFormValues()) && count($this->getFormValues()) > 0) ? true : false;
            default:
                return Gdn::request()->isPost();
        }
    }

    public function submittedValid()
    {
        if ($this->submitted()) {
            $post = $this->getFormValues();
            $secureKey = array_extract('secureKey', $post);
            if ($secureKey == $this->getSecureKey($post)) {
                return true;
            }
        }

        return false;
    }

    /**
     * return form field value
     * @param string $name field name
     * @param string $default default value if field not exists
     * @return mixed
     */
    public function getFormValue($name, $default = '')
    {
        return val($name, $this->getFormValues(), $default);
    }

    private $magicQuotes;

    /**
     * return form values
     * @return array|mixed
     */
    public function getFormValues()
    {
        if (!is_array($this->formValues)) {
            $this->magicQuotes = get_magic_quotes_gpc();

            $this->formValues = array();
            $formData = $this->method == 'get' ? $_GET : $_POST;

            $this->formValues = $this->clearFormData($formData);
        }

        return $this->formValues;
    }

    /**
     * if form submitted return post field value or return form data
     * @param string $name field name
     * @param string $default default value if field not exists
     * @return mixed
     */
    public function getValue($name, $default = '')
    {
        if ($this->submitted()) {
            return $this->getFormValue($name, $default);
        } else {
            return val($name, $this->data, $default);
        }
    }

    /**
     * return form values or if form submitted return post data
     * @return array|mixed
     */
    public function getValues()
    {
        if ($this->submitted()) {
            return $this->getFormValues();
        } else {
            return $this->data;
        }
    }

    /**
     * set form field value
     * @param string $name field value
     * @param mixed $value
     */
    public function setFormValue($name, $value)
    {
        $this->formValues[$name] = trim($value);
    }

    /**
     * check data to valid
     * @return bool|void
     */
    public function valid()
    {
        $data = $this->getFormValues();
        $data = $this->fixPostData($data);
        return $this->validation()->validate($data);
    }

    /**
     * get form errors
     * @param bool $text if true return errors in text else in html
     * @return bool|string
     */
    public function errors($text = false)
    {
        $errors = $this->validation()->errors();
        if (empty($errors)) return false;

        $html = array();
        foreach ($errors as $field => $errors) {
            foreach ($errors as $error) {
                if (is_array($error)) {
                    $errField = val(0, $error);
                    $error = val(1, $error);
                } else {
                    $errField = t($this->model ? $this->model->table : 'form' . '.' . $field, $field);
                }
                if ($text) {
                    $html[] = $errField . ' ' . $error;
                } else {
                    $html[] = '<strong>' . $errField . ':</strong> ' . $error;
                }
            }
        }

        if ($text) {
            return implode("; ", $html);
        } else {
            return sprintf(t('form_html_error_wrapper', '<div class="alert alert-danger">%s</div>'), implode("<br>", $html));
        }
    }

    /**
     * save form data
     * @return int id record
     */
    public function save()
    {
        $result = false;

        if ($this->valid()) {
            $post = $this->getFormValues();

            if ($this->model) {
                $id = val($this->model->primaryKey, $post);
                $post = $this->fixPostData($post);
                $result = $this->model->save($post, $id);
            } else {
                $result = $post;
            }
        }

        Response::current()->headers('Form-Error', !$result);

        return $result;
    }

    public function getSecureKey(array $post)
    {
        $keys = array_keys($post);
        return md5(implode($keys) . c('main.hashsalt'));
    }

    /**
     * html tag open form
     * @param array $attributes form attributes
     * @return string
     */
    public function open($attributes = [])
    {
        $return = '<form ';
        $currentPath = Gdn::request()->getPath();
        array_touch('action', $attributes, $currentPath);
        array_touch('method', $attributes, $this->method);
        $this->method = val('method', $attributes);

        $return .= $this->attrToString($attributes);

        $return .= '>';
        return $return;
    }

    /**
     * html tag close form
     * @return string
     */
    public function close()
    {
        $return = '';
        foreach ($this->hiddenInputs as $name => $value) {
            $return .= $this->input($name, 'hidden', ['value' => $value]);
        }

        $return .= $this->input('secureKey', 'hidden', ['value' => $this->getSecureKey($this->inputs)]);
        $this->inputs = [];

        $return .= '</form>';

        return $return;
    }

    /**
     * html tag input
     * @param string $name field name
     * @param string $type text|radio|checkbox|hidden|...
     * @param array $attributes input attributes
     * @return string
     */
    public function input($name, $type = 'text', $attributes = [])
    {
        if ($type !== 'radio' && $type !== 'checkbox' && $type !== 'hidden') {
            array_touch('class', $attributes, $this->inputClass);
        }

        $value = val('value', $attributes);

        if ($type == 'radio' OR $type == 'checkbox') {
            if ($value !== false && $this->getValue($name) == $value) {
                array_touch('checked', $attributes, 'checked');
            }
        }

        $attributes['name'] = $name;
        $attributes['type'] = $type;
        $attributes['value'] = $this->_value($name, $value);

        $this->inputs[$name] = $name;

        return '<input ' . $this->attrToString($attributes) . ' />';
    }

    /**
     * html tag textarea
     * @param string $name field name
     * @param array $attributes textarea attributes
     * @return string
     */
    public function textarea($name, $attributes = [])
    {
        array_touch('class', $attributes, $this->inputClass);
        array_touch('rows', $attributes, '5');

        $attributes['name'] = $name;
        $value = val('value', $attributes);
        unset($attributes['type'], $attributes['value']);

        $this->inputs[$name] = $name;

        $html = '<textarea ' . $this->attrToString($attributes) . '>';
        $html .= $this->_value($name, $value);
        $html .= '</textarea>';
        return $html;
    }

    /**
     * html tag input[type="checkbox"]
     * @param string $name field name
     * @param array $attributes checkbox attributes
     * @return string
     */
    public function checkbox($name, $attributes = [])
    {
        array_touch('value', $attributes, 1);

        $html = '<input type="hidden" name="' . $name . '" value="" />';
        $html .= $this->input($name, 'checkbox', $attributes);

        return $html;
    }

    /**
     * html tag input[type="radio"]
     * @param string $name field name
     * @param array $attributes radio attributes
     * @return string
     */
    public function radio($name, $attributes = [])
    {
        return $this->input($name, 'radio', $attributes);
    }

    /**
     * html tag select
     * @param string $name
     * @param array $options
     * @param array $attributes
     * @return string
     */
    public function select($name, $options = [], $attributes = [])
    {
        array_touch('class', $attributes, $this->inputClass);
        $attributes['name'] = $name;

        $defaultName = array_extract('default_name', $attributes);
        $defaultValue = array_extract('default_value', $attributes);
        $keyValue = array_extract('key_value', $attributes);
        $keyName = array_extract('key_name', $attributes);

        $html = '<select ' . $this->attrToString($attributes) . '>';

        if ($defaultName) {
            $html .= '<option value="' . $defaultValue . '">' . $defaultName . '</option>';
        }

        $this->inputs[$name] = $name;
        $fieldValue = $this->getValue($name);

        foreach ($options as $key => $option) {
            $value = $keyName && $keyValue ? val($keyValue, $option) : $key;
            $name = $keyName && $keyValue ? val($keyName, $option) : $option;

            $selected = is_array($fieldValue) ? in_array($value, $fieldValue) : $value == $fieldValue;

            $html .= '<option value="' . $value . '"' . ($selected ? ' selected' : '') . '>' . $name . '</option>';
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * add hidden field to form
     * @param string $name field name
     * @param mixed $value
     * @param bool $forceValue if true value will not changed after form submitted
     */
    public function addHidden($name, $value = null, $forceValue = false)
    {
        if ($this->submitted() && $forceValue === false)
            $value = $this->getFormValue($name, $value);

        $this->hiddenInputs[$name] = $value;
    }

    protected function _value($name, $value = false)
    {
        return format_form($value == false ? $this->getValue($name) : $value);
    }

    /**
     * return generated html attributes
     * @param $attributes
     * @return string
     */
    protected function attrToString($attributes)
    {
        return implode_assoc('" ', '="', $attributes) . '"';
    }

    /**
     * change date and time fields to sql format
     * @param array $post
     * @return array fixed $post data
     */
    protected function fixPostData($post)
    {
        if ($this->model) {
            unset($post[$this->model->primaryKey]);

            $structure = $this->validation()->getStructure();
            foreach ($post as $field => $value) {
                // if (empty($value)) $value = null;
                if ($value && $options = val($field, $structure)) {
                    switch ($options->dataType) {
                        case 'time':
                            $post[$field] = date_convert($value, 'time');
                            break;
                        case 'date':
                        case 'datetime':
                        case 'timestamp':
                            $post[$field] = date_convert($value, 'sql');
                            break;
                    }
                } else {
                    $post[$field] = $value;
                }
            }
        }

        return $post;
    }

    /**
     * removes extra spaces and quotes
     * @param array $formData
     * @return array $formData
     */
    protected function clearFormData($formData)
    {
        foreach ($formData as $name => $value) {
            if (is_array($value)) {
                $formData[$name] = $this->clearFormData($value);
            } else {
                $value = trim($value);
                if ($this->magicQuotes) {
                    $value = stripcslashes($value);
                }
                $formData[$name] = $value;
            }
        }

        return $formData;
    }
}