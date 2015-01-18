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
        $this->assertInternalType('string', $data);
        // parse and check data
        list($doc, $xpath) = Parser::load_html($data);
        $elements = $xpath->query('//a[@class="js-current-repository"]/text()');
        $this->assertEquals($elements->length, 1);
        $repoName = Parser::get_node_value($elements->item(0));
        $this->assertEquals($repoName, 'siflawler-php');
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
        $elements = $xpath->query('//a[@class="js-current-repository"]/text()');
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
        $elements = $xpath->query('//a[@class="url fn"]/span/text()');
        $this->assertEquals(1, $elements->length);
        $repoName = Parser::get_node_value($elements->item(0));
        $this->assertEquals('Caster', $repoName);
    }
}
