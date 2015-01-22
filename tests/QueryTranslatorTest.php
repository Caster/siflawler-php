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

            '.some-class' => "//*[(contains(concat(' ', normalize-space(@class), ' '), "
                . "' some-class '))]",

            '#someId.some-class' => "//*[@id=\"someId\" and (contains(concat(' ', "
                . "normalize-space(@class), ' '), ' some-class '))]",

            '.some-class div' => "//*[(contains(concat(' ', normalize-space(@class), "
                . "' '), ' some-class '))]/descendant::div",

            '.some-class > div#soMe' => "//*[(contains(concat(' ', normalize-space("
                . "@class), ' '), ' some-class '))]/div[@id=\"soMe\"]",

            '#some\#ID' => '//*[@id="some#ID"]',

            '#some\#\.ID' => '//*[@id="some#.ID"]',

            '#some\#.\.ID' => "//*[@id=\"some#\" and (contains(concat(' ', "
                . "normalize-space(@class), ' '), ' .ID '))]",

            '#a > #b #c' => '//*[@id="a"]/*[@id="b"]/descendant::*[@id="c"]',
        );

        foreach ($tests as $input => $expected) {
            $value = QueryTranslator::cssToXPath($input);
            $this->assertInternalType('string', $value);
            $this->assertEquals($expected, $value);
        }
    }

}
