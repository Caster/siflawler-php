<?php

namespace siflawlerTest;

require_once(__DIR__ . '/../lib/siflawler.php');

class siflawlerTest {

    public static function load($className) {
        $explodeClass = explode('\\', $className);
        if (count($explodeClass) >= 2 && $explodeClass[0] === __NAMESPACE__) {
            array_shift($explodeClass);
            require_once(__DIR__ . '/' . implode('/', $explodeClass) . '.php');
        }
    }

}

spl_autoload_register(__NAMESPACE__ . '\\siflawlerTest::load');
