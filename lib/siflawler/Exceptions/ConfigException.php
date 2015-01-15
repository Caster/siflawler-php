<?php

namespace siflawler\Exceptions;

class ConfigException extends \Exception {

    /**
     * Construct a new \siflawler\Exceptions\ConfigException with the given
     * message that is, contrary to the default \Exception class, not optional.
     *
     * @param $message Message explaining what went wrong.
     */
    public function __construct($message) {
        parent::__construct($message);
    }

}
