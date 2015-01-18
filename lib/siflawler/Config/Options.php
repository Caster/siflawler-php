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
     * Construct a new \siflawler\Config\Options that reads options from the
     * given parameter. There are multiple supported formats.
     *
     * @param $options Some form to read options from. Supported formats are:
     *            - Path to a file with configuration in JSON format.
     *            - String with JSON object with configuration.
     *            - A \stdClass object with configuration.
     *            - An associative array with configuration.
     */
    public function __construct($options) {
        // basically check which type of option is passed
        if (is_string($options)) {
            if (is_file($options)) {
                $this->construct_from_file($options);
            } else {
                $this->construct_from_string($options);
            }
        } elseif (is_object($options)) {
            $this->_options = $options;
        } elseif (is_array($options)) {
            $this->construct_from_array($options);
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


    /**
     * Load options from an associative array.
     */
    private function construct_from_array($array) {
        $this->_options = $this->array_to_object($array);
    }

    /**
     * Load options from a file, or try to at least.
     *
     * @param $file Path to a file to read.
     */
    private function construct_from_file($file) {
        // check if we can read the file
        if (!is_file($file) || !is_readable($file)) {
            throw new ConfigException('Cannot read file "' . $file . '". '
                . 'File does not exist or is not readable.');
        }

        // load settings from the file
        $this->construct_from_string(file_get_contents($file));
    }

    /**
     * Load options from a JSON-encoded object string.
     *
     * @param $json JSON as a string to decode.
     */
    private function construct_from_string($json) {
        $this->_options = json_decode($json);
        if ($this->_options === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new ConfigException('Could not parse configuration options,'
                . ' please make sure it is valid JSON (the way'
                . ' PHP understands JSON, that is).');
        }
    }

    /**
     * Convert an associative array to an object, recursively.
     *
     * @param $array Array to convert.
     */
    private function array_to_object($array) {
        $result = new \stdClass();
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result->{$k} = $this->array_to_object($v);
            } else {
                $result->{$k} = $v;
            }
        }
        return $result;
    }

}
