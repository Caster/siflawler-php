<?php

namespace siflawlerTest;

use \siflawler\Fetcher;
use \siflawler\Parser;
use \siflawler\Config\Options;

class ParserTest extends \PHPUnit_Framework_TestCase {

    private static $options;

    public static function setUpBeforeClass() {
        TestCache::init();
        self::$options = new Options(TestCache::$config_object);
        self::$options->set('next', '(//div[@id="readme"]/'
            . 'descendant::ol[@class="task-list"])[1]/descendant::li/a/@href');
    }

    public function testFind() {
        // load data
        $url = 'https://github.com/Caster/siflawler-php';
        $page = Fetcher::load(self::$options, $url);
        $this->assertInternalType('string', $page);
        // find elements in the README table of contents
        $data = array();
        $next = Parser::find(self::$options, $url, $page, $data);
        $this->assertInternalType('array', $data);
        $this->assertInternalType('array', $next);
        $this->assertEquals(6, count($next));
        $expected_next = array(
            $url . '#usage',
            $url . '#but-what-do-i-configure',
            $url . '#mandatory-options',
            $url . '#optional-options',
            $url . '#running-tests',
            $url . '#license'
        );
        for ($i = 0; $i < count($expected_next); $i++) {
            $this->assertEquals($expected_next[$i], $next[$i]);
        }
    }

}
