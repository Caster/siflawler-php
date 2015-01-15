<?php

namespace siflawler;

class siflawler {

    public static function load($className) {
        $explodeClass = explode('\\', $className);
        if (count($explodeClass) >= 2 && $explodeClass[0] === __NAMESPACE__) {
            array_shift($explodeClass);
            require_once(__DIR__ . '/' . __NAMESPACE__ . '/' .
                    implode('/', $explodeClass) . '.php');
        }
    }

}

spl_autoload_register(__NAMESPACE__ . '\\siflawler::load');
