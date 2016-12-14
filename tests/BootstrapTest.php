<?php

namespace siflawlerTest;

class BootstrapTest extends \PHPUnit_Framework_TestCase {

    public function testHasCurl() {
        if (!extension_loaded('curl')) {
            trigger_error('cURL is not available!', E_USER_ERROR);
        }
    }

}
