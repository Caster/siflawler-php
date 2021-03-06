<?php

namespace siflawlerTest;

use \siflawler\Fetcher;
use \siflawler\Parser;
use \siflawler\Config\Options;

class ParserTest extends \PHPUnit_Framework_TestCase {

    private static $next_toc;
    private static $next_menu;
    private static $options;
    private static $url;
    private static $expected_toc;
    private static $expected_menu;

    public static function setUpBeforeClass() {
        TestCache::init();

        self::$next_toc = 'xpath:(//div[@id="readme"]/'
            . 'descendant::ol)[1]/descendant::li/a/@href';
        self::$next_menu = '//nav[@class="site-header-nav site-header-nav-main"]/a/@href';
        self::$options = new Options(TestCache::$config_object);
        self::$options->set('next', self::$next_toc);
        self::$url = 'https://github.com/Caster/siflawler-php';
        self::$expected_toc = array(
            self::$url . '#dependencies',
            self::$url . '#usage',
            self::$url . '#configuration',
            self::$url . '#mandatory-options',
            self::$url . '#optional-options',
            self::$url . '#querying',
            self::$url . '#running-tests',
            self::$url . '#contributing',
            self::$url . '#license'
        );
        self::$expected_menu = array(
            'https://github.com/personal',
            'https://github.com/open-source',
            'https://github.com/business',
            'https://github.com/explore'
        );
    }

    public function testFind() {
        // load data
        $page = Fetcher::load(self::$options, self::$url);
        $this->assertInternalType('array', $page);
        $this->assertEquals(1, count($page));
        $page = $page[0];
        $this->assertInternalType('string', $page);

        // find elements in the README table of contents
        $data = array();
        $next = Parser::find(self::$options, self::$url, $page, $data);
        $this->assertInternalType('array', $data);
        $this->assertInternalType('array', $next);
        $this->assertEquals(count(self::$expected_toc), count($next));
        for ($i = 0; $i < count(self::$expected_toc); $i++) {
            $this->assertEquals(self::$expected_toc[$i], $next[$i]);
        }

        // find elements in the GitHub menu
        self::$options->set('next', self::$next_menu);
        $data = array();
        $next = Parser::find(self::$options, self::$url, $page, $data);
        $this->assertInternalType('array', $data);
        $this->assertInternalType('array', $next);
        $this->assertEquals(4, count($next));
        for ($i = 0; $i < count(self::$expected_menu); $i++) {
            $this->assertEquals(self::$expected_menu[$i], $next[$i]);
        }
        self::$options->set('next', self::$next_toc);
    }

    public function testFindMultiple() {
        // load data
        $url = array(self::$url, self::$url);
        $page = Fetcher::load(self::$options, $url);
        $this->assertInternalType('array', $page);
        // find elements in the README table of contents
        $data = array();
        $next = Parser::find(self::$options, $url, $page, $data);
        $this->assertInternalType('array', $data);
        $this->assertInternalType('array', $next);
        $this->assertEquals(count(self::$expected_toc) * 2, count($next));
        $expected_next = array_merge(self::$expected_toc, self::$expected_toc);
        for ($i = 0; $i < count($expected_next); $i++) {
            $this->assertEquals($expected_next[$i], $next[$i]);
        }
    }

}
