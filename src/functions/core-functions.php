<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license MIT
 */


/**
 * Base64 Encode a string, but make it suitable to be passed in a url.
 *
 * @param string $str The string to encode.
 * @return string Returns the encoded string.
 * @category String Functions
 * @see base64_urldecode()
 * @see base64_encode()
 */
function base64url_encode($str) {
    return trim(strtr(base64_encode($str), '+/', '-_'), '=');
}

/**
 * Decode a string that was encoded using {@link base64_urlencode()}.
 *
 * @param string $str The encoded string.
 * @return string The decoded string.
 * @category String Functions
 * @see base64_urldecode()
 * @see base64_decode()
 */
function base64url_decode($str) {
    return base64_decode(strtr($str, '-_', '+/'));
}

/**
 * An alias of {@link config()}.
 *
 * @param string $key The config key.
 * @param string $default The default value if the config setting isn't available.
 * @return string The config value.
 * @see config()
 */
function c($key = false, $default = null) {
    $data = Garden\Config::data();
    return valr($key, $data, $default);
}

/**
 * Get a value from the config.
 *
 * @param string $key The config key.
 * @param mixed $default The default value if the config setting isn't available.
 * @return mixed The config value.
 */
function config($group, $key = false, $default = null) {
    return Garden\Config::get($group, $key, $default);
}

/**
 * Mark something as deprecated.
 *
 * When passing the {@link $name} argument, try using the following naming convention for names.
 *
 * - Functions: function_name()
 * - Classes: ClassName
 * - Static methods: ClassName::methodName()
 * - Instance methods: ClassName->methodName()
 *
 * @param string $name The name of the deprecated function.
 * @param string $newname The name of the new function that should be used instead.
 */
function deprecated($name, $newname = '') {
    $msg = $name.' is deprecated.';
    if ($newname) {
        $msg .= " Use $newname instead.";
    }

    trigger_error($msg, E_USER_DEPRECATED);
}

/**
 * A version of file_put_contents() that is multi-thread safe.
 *
 * @param string $filename Path to the file where to write the data.
 * @param mixed $data The data to write. Can be either a string, an array or a stream resource.
 * @param int $mode The permissions to set on a new file.
 * @return boolean
 * @category Filesystem Functions
 * @see http://php.net/file_put_contents
 */
function file_put_contents_safe($filename, $data, $mode = 0644) {
    $temp = tempnam(dirname($filename), 'atomic');

    if (!($fp = @fopen($temp, 'wb'))) {
        $temp = dirname($filename).DIRECTORY_SEPARATOR.uniqid('atomic');
        if (!($fp = @fopen($temp, 'wb'))) {
            trigger_error("file_put_contents_safe() : error writing temporary file '$temp'", E_USER_WARNING);
            return false;
        }
    }

    fwrite($fp, $data);
    fclose($fp);

    if (!@rename($temp, $filename)) {
        @unlink($filename);
        @rename($temp, $filename);
    }

    @chmod($filename, $mode);
    return true;
}

/**
 * Force a value into a boolean.
 *
 * @param mixed $value The value to force.
 * @return boolean Returns the boolean value of {@link $value}.
 * @category Type Functions
 */
function force_bool($value) {
    if (is_string($value)) {
        switch (strtolower($value)) {
            case 'disabled':
            case 'false':
            case 'no':
            case 'off':
            case '':
                return false;
        }
        return true;
    }
    return (bool)$value;
}

/**
 * Force a string to look like an ip address (v4).
 *
 * @param string $ip The ip string to look at.
 * @return string|null The ipv4 address or null if {@link $ip} is empty.
 */
function force_ipv4($ip) {
    if (!$ip) {
        return null;
    }

    if (strpos($ip, ',') !== false) {
        $ip = substr($ip, 0, strpos($ip, ','));
    }

    // Make sure we have a valid ip.
    if (preg_match('`(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})`', $ip, $m)) {
        $ip = $m[1];
    } elseif ($ip === '::1') {
        $ip = '127.0.0.1';
    } else {
        $ip = '0.0.0.0'; // unknown ip
    }
    return $ip;
}

/**
 * Force a value to be an integer.
 *
 * @param mixed $value The value to force.
 * @return int Returns the integer value of {@link $value}.
 * @category Type Functions
 */
function force_int($value) {
    if (is_string($value)) {
        switch (strtolower($value)) {
            case 'disabled':
            case 'false':
            case 'no':
            case 'off':
            case '':
                return 0;
            case 'enabled':
            case 'true':
            case 'yes':
            case 'on':
                return 1;
        }
    }
    return intval($value);
}

function garden_error_handler($number, $message, $file, $line, $args) {
    $error_reporting = error_reporting();
    // Ignore errors that are below the current error reporting level.
    if (($error_reporting & $number) != $number) {
        return false;
    }

    $backtrace = debug_backtrace();

    throw new Garden\Exception\Error($message, $number, $file, $line, $args, $backtrace);
}



/**
 * Strip a substring from the beginning of a string.
 *
 * @param string $mainstr The main string to look at (the haystack).
 * @param string $substr The substring to search trim (the needle).
 * @return string
 *
 * @category String Functions
 */
function ltrim_substr($mainstr, $substr) {
    if (strncasecmp($mainstr, $substr, strlen($substr)) === 0) {
        return substr($mainstr, strlen($substr));
    }
    return $mainstr;
}

/**
 * Get the file extension from a mime-type.
 *
 * @param string $mime The mime type.
 * @param string $ext If this argument is specified then this extension will be added to the list of known types.
 * @return string The file extension without the dot.
 * @category Internet Functions
 * @category String Functions
 */
function mime2ext($mime, $ext = null) {
    static $known = array('text/plain' => '.txt', 'image/jpeg' => '.jpg', 'application/rss+xml' => '.rss');
    $mime = strtolower($mime);

    if ($ext !== null) {
        $known[$mime] = '.'.ltrim($ext, '.');
    }

    if (array_key_exists($mime, $known)) {
        return $known[$mime];
    }

    // We don't know the mime type so we need to just return the second part as the extension.
    $result = trim(strrchr($mime, '/'), '/');

    if (substr($result, 0, 2) === 'x-') {
        $result = substr($result, 2);
    }

    return '.'.$result;
}

/**
 * Encode a php array nicely.
 *
 * @param array $data The data to encode.
 * @param string $php_var The name of the php variable.
 * @return string Returns a string of the encoded data.
 *
 * @category Array Functions
 */
function php_encode($data, $php_var = 'config') {
    if (is_array($data)) {
        $result = '';
        $lastHeading = '';
        foreach ($data as $key => $value) {
            // Figure out the heading.
            if (($pos = strpos($key, '.')) !== false) {
                $heading = str_replace(array("\n", "\r"), ' ', substr($key, 0, $pos));
            } else {
                $heading = substr($key, 0, 1);
            }

            if ($heading !== $lastHeading) {
                if (strlen($heading) === 1) {
                    // Don't emit single letter headings, but space them out.
                    $result .= "\n";
                } else {
                    $result .= "\n// ".$heading."\n";
                }
                $lastHeading = $heading;
            }

            $result .= '$'.$php_var.'['.var_export($key, true).'] = '.var_export($value, true).";\n";
        }
    } else {
        $result = "\$$php_var = ".var_export($data, true).";\n";
    }
    return $result;
}

/**
 * Reflect the arguments on a callback and returns them as an associative array.
 *
 * @param callable $callback A callback to the function.
 * @param array $args An array of arguments.
 * @param array $get An optional other array of arguments.
 * @return array The arguments in an associative array, in order ready to be passed to call_user_func_array().
 * @throws Exception Throws an exception when {@link callback} isn't a valid callback.
 * @category Type Functions
 */
function reflect_args(callable $callback, $args, $get = null) {
    if (is_array($get)) {
        $args = array_merge($get, $args);
    }
    $args = array_change_key_case($args);

    if (is_string($callback) || (is_object($callback) && $callback instanceof Closure)) {
        $meth = new ReflectionFunction($callback);
        $meth_name = $meth;
    } else {
        $meth = new ReflectionMethod($callback[0], $callback[1]);
        if (is_string($callback[0])) {
            $meth_name = $callback[0].'::'.$meth->getName();
        } else {
            $meth_name = get_class($callback[0]).'->'.$meth->getName();
        }
    }

    $meth_params = $meth->getParameters();

    $call_args = array();
    $missing_args = array();

    // Set all of the parameters.
    foreach ($meth_params as $index => $meth_param) {
        $param_name = $meth_param->getName();
        $param_namel = strtolower($param_name);

        if (isset($args[$param_namel])) {
            $param_value = $args[$param_namel];
        } elseif (isset($args[$index])) {
            $param_value = $args[$index];
        } elseif ($meth_param->isDefaultValueAvailable()) {
            $param_value = $meth_param->getDefaultValue();
        } else {
            $param_value = null;
            $missing_args[] = '$'.$param_name;
        }

        $call_args[$param_name] = $param_value;
    }

    // Add optional parameters so that methods that use get_func_args() will still work.
    for ($index = count($call_args); array_key_exists($index, $args); $index++) {
        $call_args[$index] = $args[$index];
    }

    if (count($missing_args) > 0) {
        trigger_error("$meth_name() expects the following parameters: ".implode(', ', $missing_args).'.', E_USER_NOTICE);
    }

    return $call_args;
}

/**
 * Strip a substring rom the end of a string.
 *
 * @param string $mainstr The main string to search (the haystack).
 * @param string $substr The substring to trim (the needle).
 * @return string Returns the trimmed string or {@link $mainstr} if {@link $substr} was not found.
 * @category String Functions
 */
function rtrim_substr($mainstr, $substr) {
    if (strcasecmp(substr($mainstr, -strlen($substr)), $substr) === 0) {
        return substr($mainstr, 0, -strlen($substr));
    }
    return $mainstr;
}

/**
 * Returns whether or not a string begins with another string.
 *
 * This function is not case-sensitive.
 *
 * @param string $haystack The string to test.
 * @param string $needle The substring to test against.
 * @return bool Whether or not `$string` begins with `$with`.
 * @category String Functions
 */
function str_begins($haystack, $needle) {
    return strncasecmp($haystack, $needle, strlen($needle)) === 0;
}

/**
 * Returns whether or not a string ends with another string.
 *
 * This function is not case-sensitive.
 *
 * @param string $haystack The string to test.
 * @param string $needle The substring to test against.
 * @return bool Whether or not `$string` ends with `$with`.
 * @category String Functions
 */
function str_ends($haystack, $needle) {
    return strcasecmp(substr($haystack, -strlen($needle)), $needle) === 0;
}

/**
 * Translate a string.
 *
 * @param string $code The translation code.
 * @param string $default The default if the translation is not found.
 * @return string The translated string.
 *
 * @category String Functions
 * @category Localization Functions
 */
function t($code, $default = null) {
    return \Garden\Gdn::translate($code, $default);
}

/**
 * A version of {@link sprintf()} That translates the string format.
 *
 * @param string $formatCode The format translation code.
 * @param mixed $arg1 The arguments to pass to {@link sprintf()}.
 * @return string The translated string.
 */
function t_sprintf($formatCode, $arg1 = null) {
    $args = func_get_args();
    $args[0] = t($formatCode);
    return call_user_func_array('sprintf', $args);
}

/**
 * Make sure that a directory exists.
 *
 * @param string $dir The name of the directory.
 * @param int $mode The file permissions on the folder if it's created.
 * @throws Exception Throws an exception when {@link $dir} is a file.
 * @category Filesystem Functions
 */
function touchdir($dir, $mode = 0777) {
    if (!file_exists($dir)) {
        mkdir($dir, $mode, true);
    } elseif (!is_dir($dir)) {
        throw new Exception("The specified directory already exists as a file. ($dir)", 400);
    }
}

/**
 * Safely get a value out of an array.
 *
 * This function will always return a value even if the array key doesn't exist.
 * The val() function is one of the biggest workhorses of Vanilla and shows up a lot throughout other code.
 * It's much preferable to use this function if your not sure whether or not an array key exists rather than
 * using @ error suppression.
 *
 * This function uses optimizations found in the [facebook libphputil library](https://github.com/facebook/libphutil).
 *
 * @param string|int $key The array key.
 * @param array|object $array The array to get the value from.
 * @param mixed $default The default value to return if the key doesn't exist.
 * @return mixed The item from the array or `$default` if the array key doesn't exist.
 * @category Array Functions
 */
function val($key, $array, $default = false) {
    if (is_array($array)) {
        // isset() is a micro-optimization - it is fast but fails for null values.
        if (isset($array[$key])) {
            return $array[$key];
        }

        // Comparing $default is also a micro-optimization.
        if ($default === null || array_key_exists($key, $array)) {
            return null;
        }
    } elseif (is_object($array)) {
        if (isset($array->$key)) {
            return $array->$key;
        }

        if ($default === null || property_exists($array, $key)) {
            return null;
        }
    }

    return $default;
}

/**
 * Return the value from an associative array.
 *
 * This function differs from val() in that $key can be an array that will be used to walk a nested array.
 *
 * @param array|string $keys The keys or property names of the value. This can be an array or dot-seperated string.
 * @param array|object $array The array or object to search.
 * @param mixed $default The value to return if the key does not exist.
 * @return mixed The value from the array or object.
 * @category Array Functions
 */
function valr($keys, $array, $default = false) {
    if (is_string($keys)) {
        $keys = explode('.', $keys);
    }

    $value = $array;
    for ($i = 0; $i < count($keys); ++$i) {
        $SubKey = $keys[$i];

        if (is_array($value) && isset($value[$SubKey])) {
            $value = $value[$SubKey];
        } elseif (is_object($value) && isset($value->$SubKey)) {
            $value = $value->$SubKey;
        } else {
            return $default;
        }
    }
    return $value;
}

/**
 * Set the value on an object/array.
 *
 * @param string $Needle The key or property name of the value.
 * @param mixed $Haystack The array or object to set.
 * @param mixed $Value The value to set.
 */
function setval($Key, &$Collection, $Value)
{
    if(is_array($Collection))
        $Collection[$Key] = $Value;
    elseif(is_object($Collection))
        $Collection->$Key = $Value;
}

function getInclude($path, $data = array())
{
    ob_start();
    extract($data);
    
    include $path;

    $result = ob_get_contents(); 
    ob_end_clean();

    return $result;
}

function redirect($url, $code = 302)
{
    $host = Garden\Request::current()->getHost();
    $scheme = Garden\Request::current()->getScheme();

    $url = is_url($url) ? $url : $scheme.'://'.$host.$url;

    header("Location: $url");

    exit;
}