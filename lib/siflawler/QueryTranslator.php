<?php

namespace siflawler;

/**
 * This class provides a static method to translate a string with a CSS query
 * into an XPath query. There is also support for some extensions, to be able
 * to query attribute values and text for example.
 */
class QueryTranslator {

    /**
     * Regular expression to match CSS identifiers (class names and IDs).
     * TODO: this should be extended somehow, because more is allowed. Refer to
     *       http://stackoverflow.com/a/6732899/962603 and
     *       https://mathiasbynens.be/notes/css-escapes.
     */
    private static $css_identifier_regex = '[A-Za-z0-9_\-]+';

    /**
     * Translate a CSS selector to an XPath query.
     *
     * @param $css Selector to translate.
     */
    public static function cssToXPath($css) {
        $cssSplit = preg_split('/\s*,\s*/', $css);
        $xSplit = array_map(array(get_called_class(), 'singleCssToXPath'), $cssSplit);
        return implode(' | ', $xSplit);
    }


    /**
     * Translate a single CSS selector (no commas) to XPath.
     *
     * @param $css Selector to translate.
     */
    private static function singleCssToXPath($css) {
        // TODO: split on whitespace for descendant queries, check for > for
        // child-of queries, ...

        $xPath = '//';
        $idSplit = preg_split('/(?<!\\\\)#/', $css);
        if ($idSplit[0] !== '') {
            $xPath .= $idSplit[0];
        } else {
            $xPath .= '*';
        }
        array_shift($idSplit);
        if (count($idSplit) > 0) {
            $xPath .= '[@id="';
            $xPath .= implode('" and @id="', $idSplit);
            $xPath .= '"]';
        }
        // TODO: classes

        return $xPath;
    }

}
