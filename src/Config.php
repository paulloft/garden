<?php

namespace Garden;

/**
 * Application configuration management.
 *
 * This class provides access to the application configuration information through one or more config files.
 * You can load/save config files in several formats. The file extension of the file determines what format will the file will use.
 * The following file formats are supported.
 *
 * - javascript object notation (json): .json or .json.php
 * - php source code: .php
 * - php serialized arrays: .ser or .ser.php
 * - yaml: .yml or .yml.php
 *
 * When using config files we recommend always using the .*.php extension so that the file cannot be read through its url.
 *
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009 Vanilla Forums Inc.
 * @license LGPL-2.1
 * @package Vanilla
 * @since 1.0
 */
class Config {
    /// Properties ///

    /**
     * @var array The config data.
     */
    protected static $data = [];

    public static $cached = false;

    /**
     * @var string The default path to load/save to.
     */
    protected static $defaultPath;

    protected static $coreConfig = PATH_SRC.'/conf';

    /// Methods ///

    /**
     * Get or set the default path.
     *
     * @param string $value Pass a value to set a new default path.
     * @return string Returns the current default path.
     */
    public static function defaultPath($value = '') {
        if ($value) {
            self::$defaultPath = $value;
        } elseif (!self::$defaultPath) {
            self::$defaultPath = PATH_CONF.'/config.php';
        }
        return self::$defaultPath;
    }

    /**
     * Return all of the config data.
     *
     * @return array Returns an array of config data.
     */
    public static function data() {
        return self::$data;
    }

    /**
     * Get a setting from the config.
     *
     * @param string $key The config key.
     * @param mixed $default The default value if the config file doesn't exist.
     * @return mixed The value at {@link $key} or {@link $default} if the key isn't found.
     * @see \config()
     */
    public static function get($group, $key = false, $default = null) {
        if (array_key_exists($group, self::$data)) {
            $data = self::$data[$group];
        } else {
            return $default;
        }

        if($key === false) {
            return $data;
        } elseif (array_key_exists($key, $data)) {
            return $data[$key];
        } else {
            return $default;
        }
    }

    /**
     * Load configuration data from a file.
     *
     * @param string $path An optional path to load the file from.
     * @param string $path If true the config will be put under the current config, not over it.
     */
    public static function load($group, $path = '', $underlay = false) {
        if (!$path) {
            $path = self::defaultPath();
        }

        $loaded = array_load($path);

        if (empty($loaded)) {
            return;
        }

        if (!isset(self::$data[$group])) {
            self::$data[$group] = [];
        }

        if ($underlay) {
            $data = array_replace($loaded, self::$data[$group]);
        } else {
            $data = array_replace(self::$data[$group], $loaded);
        }
        self::$data[$group] = $data;
    }

    /**
     * Save data to the config file.
     *
     * @param array $data The config data to save.
     * @param string $path An optional path to save the data to.
     * @param string $php_var The name of the php variable to load from if using the php file type.
     * @return bool Returns true if the save was successful or false otherwise.
     * @throws \InvalidArgumentException Throws an exception when the saved data isn't an array.
     */
    public static function save($data, $path = null, $php_var = 'config') {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Config::save(): Argument #1 is not an array.', 400);
        }

        if (!$path) {
            $path = static::defaultPath();
        }

        // Load the current config information so we know what to replace.
        $config = array_load($path, $php_var);
        // Merge the new config into the current config.
        $config = array_replace($config, $data);
        // Remove null config values.
        $config = array_filter($config, function ($value) {
            return $value !== null;
        });

        ksort($config, SORT_NATURAL | SORT_FLAG_CASE);

        $result = array_save($config, $path, $php_var);
        return $result;
    }

    /**
     * Autoad config
     * @param string $path
     */
    public static function autoload($path = PATH_CONF) {
        $cached = Gdn::cache('rough')->get('config-autoload');
        if(!$cached) {
            // load default configs from $coreConfig
            if($path !== self::$coreConfig) {
                Config::autoload(self::$coreConfig);
            }

            $dir = scandir($path);
            foreach ($dir as $filename) {
                if($filename == '.' OR $filename == '..') {
                    continue;
                }
                $file = $path.'/'.$filename;
                $info = pathinfo($filename);
                $group = val('filename', $info);

                if($group) {
                    self::load($group, $file);
                }
            }
        } else {
            self::$data = $cached; 
        }
    }

    /**
     * caching all configs
     */
    public static function cache() {
        if(!Gdn::cache('rough')->get('config-autoload')) {
            Gdn::cache('rough')->set('config-autoload', self::$data);
        }
    }
}
