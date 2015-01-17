<?php

namespace siflawlerTest;

class CrawlerTest extends \PHPUnit_Framework_TestCase {

    public function testConstruct() {
        try {
            $crawler = new \siflawler\Crawler(__DIR__ . '/siflawler-php-github.json');
        } catch (Exception $e) {
            $this->fail('\\siflawler\\Crawler threw an exception ("' . $e->getMessage() . '").');
        }
    }

    public function testCrawl() {
        $crawler = new \siflawler\Crawler(__DIR__ . '/siflawler-php-github.json');
        $data = $crawler->crawl();
        $this->assertEquals(count($data), 1);
        $this->assertEquals($data[0]->language, 'PHP');
        $this->assertEquals($data[0]->percent, '100%');
    }

}
