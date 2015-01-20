<?php

namespace siflawlerTest;

use \siflawler\QueryTranslator;

class QueryTranslatorTest extends \PHPUnit_Framework_TestCase {

    public function testCssToXPath() {
        $tests = array(
            '#some-id' => '//*[@id="some-id"]',
            '#someID' => '//*[@id="someID"]',
            '#someID#some-otherID' => '//*[@id="someID" and @id="some-otherID"]',
            'div#some-id' => '//div[@id="some-id"]',
            '#some-id, p#some-other-id' => '//*[@id="some-id"] | //p[@id="some-other-id"]',
            '.some-class' => '//*[@class="some-class"]',
        );

        foreach ($tests as $input => $expected) {
            $value = QueryTranslator::cssToXPath($input);
            $this->assertInternalType('string', $value);
            $this->assertEquals($expected, $value);
        }
    }

}
