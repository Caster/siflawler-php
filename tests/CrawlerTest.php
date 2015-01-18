<?php

namespace siflawlerTest;

class CrawlerTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        TestCache::init();
    }

    public function testConstruct() {
        try {
            $crawler = new \siflawler\Crawler(TestCache::$config_file);
        } catch (Exception $e) {
            $this->fail('\\siflawler\\Crawler threw an exception ("' . $e->getMessage() . '").');
        }
    }

    public function testCrawlFile() {
        $crawler = new \siflawler\Crawler(TestCache::$config_file);
        $this->runAndVerifyCrawler($crawler);
    }

    public function testCrawlString() {
        $crawler = new \siflawler\Crawler(TestCache::$config_string);
        $this->runAndVerifyCrawler($crawler);
    }

    public function testCrawlObject() {
        $crawler = new \siflawler\Crawler(TestCache::$config_object);
        $this->runAndVerifyCrawler($crawler);
    }

    public function testCrawlArray() {
        $crawler = new \siflawler\Crawler(TestCache::$config_array);
        $this->runAndVerifyCrawler($crawler);
    }

    protected function runAndVerifyCrawler($crawler) {
        $data = $crawler->crawl();
        $this->assertEquals(count($data), 1);
        $this->assertEquals($data[0]->language, 'PHP');
        $this->assertEquals($data[0]->percent, '100%');
    }

}
