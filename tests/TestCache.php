<?php

namespace siflawlerTest;

use \siflawler\NotFoundException;

/**
 * This class contains some variables that can be used in multiple tests.
 */
class TestCache {

    private static $initialised = false;

    public static $config_file;
    public static $config_string;
    public static $config_object;
    public static $config_array;
    public static $config_file_rijdendetreinen;
    public static $local_file;

    public static function init() {
        // try to be smart and save CPU cycles
        if (self::$initialised) {
            return;
        }

        // files to read configuration from
        self::$config_file = __DIR__
            . '/siflawler-config/siflawler-php-github.json';
        self::$config_file_rijdendetreinen = __DIR__
            . '/siflawler-config/rijdendetreinen.json';

        // same configuration, as a string
        // note that we avoid file_get_contents because technically, that may do
        // something with the data, plus we use that exact function when reading
        // from a file already in siflawler, so actually do something different
        self::$config_string = <<<JSON
{
    "verbose": false,
    "warnings": false,
    "start": "https://github.com/Caster/siflawler-php",
    "find": "//ol[@class=\"repository-lang-stats-numbers\"]/li",
    "get": {
        "language": "//a/span[@class=\"lang\"]/text()",
        "percent": "//a/span[@class=\"percent\"]/text()"
    },
    "next": null
}
JSON;

        // construct the configuration object as a \stdClass object... tedious
        self::$config_object = new \stdClass();
        self::$config_object->verbose = false;
        self::$config_object->warnings = false;
        self::$config_object->start = 'https://github.com/Caster/siflawler-php';
        self::$config_object->find = '//ol[@class="repository-lang-stats-numbers"]/li';
        self::$config_object->get = new \stdClass();
        self::$config_object->get->language = '//a/span[@class="lang"]/text()';
        self::$config_object->get->percent = '//a/span[@class="percent"]/text()';
        self::$config_object->next = null;

        // construct the configuration object as an array... slightly less tedious
        self::$config_array = array(
            'verbose' => false,
            'warnings' => false,
            'start' => 'https://github.com/Caster/siflawler-php',
            'find' => 'ol.repository-lang-stats-numbers > li',
            'get' => array(
                'language' => 'a > span.lang$text()',
                'percent' => 'a > span.percent$text()'
            ),
            'next' => null
        );

        // set file where local copy can be found/stored
        self::$local_file = __DIR__ .
            '/siflawler-config/github-siflawler-php.html';
        if (!file_exists(self::$local_file)) {
            self::download_local_copy();
        }

        // done!
        self::$initialised = true;
    }


    private function download_local_copy() {
        $f = @file_get_contents('https://github.com/Caster/siflawler-php');
        if ($f !== false) {
            if (@file_put_contents(self::$local_file, $f) !== false) {
                return;
            }
        }
        throw new NotFoundException('could not download or save ' .
            'siflawler GitHub page for local testing');
    }

}
