<?php

namespace siflawlerTest;

use \siflawler\Crawler;

class CrawlerTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        TestCache::init();
    }

    public function testConstruct() {
        try {
            $crawler = new Crawler(TestCache::$config_file);
        } catch (Exception $e) {
            $this->fail('\\siflawler\\Crawler threw an exception ("' . $e->getMessage() . '").');
        }
    }

    public function testCrawlFile() {
        $crawler = new Crawler(TestCache::$config_file);
        $this->runAndVerifyCrawler($crawler);
    }

    public function testCrawlString() {
        $crawler = new Crawler(TestCache::$config_string);
        $this->runAndVerifyCrawler($crawler);
    }

    public function testCrawlObject() {
        $crawler = new Crawler(TestCache::$config_object);
        $this->runAndVerifyCrawler($crawler);
    }

    public function testCrawlArray() {
        $crawler = new Crawler(TestCache::$config_array);
        $this->runAndVerifyCrawler($crawler);
    }

    protected function runAndVerifyCrawler($crawler) {
        $data = $crawler->crawl();
        $this->assertEquals(1, count($data));
        $this->assertEquals('PHP', $data[0]->language);
        $this->assertEquals('100.0%', $data[0]->percent);
    }

}
