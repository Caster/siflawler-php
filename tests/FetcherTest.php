<?php

namespace siflawlerTest;

use \siflawler\Fetcher;
use \siflawler\Parser;
use \siflawler\Config\Options;

class FetcherTest extends \PHPUnit_Framework_TestCase {

    private static $options;

    public static function setUpBeforeClass() {
        TestCache::init();
        self::$options = new Options(TestCache::$config_object);
    }

    public function testLoad() {
        // load data
        $data = Fetcher::load(self::$options, 'https://github.com/Caster/siflawler-php');
        $this->assertInternalType('array', $data);
        $this->assertEquals(1, count($data));
        $data = $data[0];
        $this->assertInternalType('string', $data);
        // parse and check data
        $this->checkSiflawlerPage($data);
    }

    public function testLoadLocal() {
        // load data
        $data = Fetcher::load(self::$options, TestCache::$local_file);
        $this->assertInternalType('array', $data);
        $this->assertEquals(1, count($data));
        $data = $data[0];
        $this->assertInternalType('string', $data);
        // parse and check data
        $this->checkSiflawlerPage($data);
    }

    public function testLoadMix() {
        // load data
        $data = Fetcher::load(self::$options, array(
            'https://github.com/Caster/siflawler-php',
            TestCache::$local_file));
        $this->assertInternalType('array', $data);
        $this->assertEquals(2, count($data));
        foreach ($data as $page) {
            $this->assertInternalType('string', $page);
            // parse and check data
            $this->checkSiflawlerPage($page);
        }
    }

    public function testLoadMulti() {
        // load data
        $data = Fetcher::load(self::$options, array(
            'https://github.com/Caster/siflawler-php',
            'https://github.com/blog',
            'https://github.com/Caster/siflawler-php'
        ));
        $this->assertInternalType('array', $data);
        $this->assertEquals(3, count($data));

        // parse and check repository name from first URL
        list($doc, $xpath) = Parser::load_html($data[0]);
        $elements = $xpath->query('//strong[@itemprop="name"]/a/text()');
        $this->assertEquals(1, $elements->length);
        $repoName = Parser::get_node_value($elements->item(0));
        $this->assertEquals('siflawler-php', $repoName);

        // parse and check blog name from second URL
        list($doc, $xpath) = Parser::load_html($data[1]);
        $elements = $xpath->query('//a[@class="blog-title"]/text()');
        $this->assertEquals(1, $elements->length);
        $repoName = Parser::get_node_value($elements->item(0));
        $this->assertEquals('The GitHub Blog', $repoName);

        // parse and check owner name from third URL
        list($doc, $xpath) = Parser::load_html($data[2]);
        $elements = $xpath->query('//a[@class="url fn"]/text()');
        $this->assertEquals(1, $elements->length);
        $repoName = Parser::get_node_value($elements->item(0));
        $this->assertEquals('Caster', $repoName);
    }

    /**
     * Check if the given page has certain elements.
     */
    private function checkSiflawlerPage($data) {
        list($doc, $xpath) = Parser::load_html($data);
        $elements = $xpath->query('//strong[@itemprop="name"]/a/text()');
        $this->assertEquals(1, $elements->length);
        $repoName = Parser::get_node_value($elements->item(0));
        $this->assertEquals('siflawler-php', $repoName);
    }

}
