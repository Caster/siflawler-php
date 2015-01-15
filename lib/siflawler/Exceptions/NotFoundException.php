<?php

namespace siflawler\Exceptions;

class NotFoundException extends \Exception {

    /**
     * Construct a new \siflawler\Exceptions\NotFoundException with the given
     * message that is, contrary to the default \Exception class, not optional.
     *
     * @param $message Message explaining what went wrong.
     */
    public function __construct($message) {
        parent::__construct($message);
    }

}
