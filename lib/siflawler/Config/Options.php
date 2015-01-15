<?php

namespace siflawler\Config;

use \siflawler\Exceptions\ConfigException;

/**
 * Class that can read a configuration file in JSON format and functions as a
 * dictionary with configuration options. It also provides default values for
 * some configuration options.
 */
class Options {

    /**
     * Standard object with default options.
     */
    private $_default_options;
    /**
     * Standard object with options.
     */
    private $_options;

    /**
     * Construct a new \siflawler\Options that reads settings from the given
     * file, in JSON format.
     *
     * @param $configFile Path to a file with configuration in JSON format.
     */
    public function __construct($configFile) {
        // check if we can read the file
        if (!is_file($configFile) || !is_readable($configFile)) {
            throw new ConfigException('Cannot read file "' . $configFile . '". '
                . 'File does not exist or is not readable.');
        }

        // load settings from the file
        $this->_options = json_decode(file_get_contents($configFile));
        if ($this->_options === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigException('Could not parse configuration file "'
                . $configFile . '", please make sure it is valid JSON (the way'
                . ' PHP understands JSON, that is).');
        }

        // check mandatory options
        foreach (array('start', 'find', 'get') as $mandatory_option) {
            if (!property_exists($this->_options, $mandatory_option)) {
                throw new ConfigException('Missing mandatory option "'
                    . $mandatory_option . '" in the configuration file.');
            }
        }

        // load default options
        $this->_default_options = json_decode(file_get_contents(__DIR__ . '/default.json'));
        if ($this->_default_options === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigException('Could not read default option values.');
        }
    }

    /**
     * Return the value associated with the given key in the configuration.
     *
     * @param $key Key to look for.
     * @param $default (optional) Default value to return in case the key does
     *            not exist in the configuration.
     */
    public function get($key) {
        // check options dictionary
        if (property_exists($this->_options, $key)) {
            return $this->_options->{$key};
        }
        // check default argument
        if (func_num_args() > 1) {
            return func_get_arg(1);
        }
        // check default options dictionary
        if (property_exists($this->_default_options, $key)) {
            return $this->_default_options->{$key};
        }
        // not found anywhere, throw exception
        throw new ConfigException('Invalid key "' . $key . '".');
    }

    /**
     * Change a value in the configuration.
     *
     * @param $key The key of the value to change. Does not need to exist.
     * @param $value The (new) value to assosicate with that key.
     */
    public function set($key, $value) {
        $this->_options->{$key} = $value;
    }

}
