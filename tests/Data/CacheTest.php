<?php

namespace siflawlerTest\Data;

use \siflawler\Crawler;
use \siflawlerTest\TestCache;

class CacheTest extends \PHPUnit_Framework_TestCase {

    public static function setUpBeforeClass() {
        TestCache::init();
    }

    public function testCrawlMulti() {
        $crawler = new Crawler(TestCache::$config_file_rijdendetreinen);
        list($data, $visited) = $crawler->crawl(null, true);
        $this->assertInternalType('array', $data);
        $this->assertEquals(150, count($data));
        // Note: without the cache, some pages would be parsed more than once
        //       and we would have had way more elements in the results... but
        //       many of them would be the same.
        $this->assertInternalType('array', $visited);
        $this->assertEquals(13, count($visited));
        $visited_expected = array(
            'http://www.rijdendetreinen.nl/storingen/p550',
            'http://www.rijdendetreinen.nl/storingen/p551',
            'http://www.rijdendetreinen.nl/storingen/p552',
            'http://www.rijdendetreinen.nl/storingen/p553',
            'http://www.rijdendetreinen.nl/storingen/p554',
            'http://www.rijdendetreinen.nl/storingen/p555',
            'http://www.rijdendetreinen.nl/storingen/p556',
            'http://www.rijdendetreinen.nl/storingen/p557',
            'http://www.rijdendetreinen.nl/storingen/p558',
            'http://www.rijdendetreinen.nl/storingen/p559',
            'http://www.rijdendetreinen.nl/storingen/p560',
            'http://www.rijdendetreinen.nl/storingen/p561',
            'http://www.rijdendetreinen.nl/storingen/p562'
        );
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals($visited_expected[$i], $visited[$i]);
        }
    }

}
