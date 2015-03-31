<?php

namespace siflawler;

/**
 * This class provides a static method to translate a string with a CSS query
 * into an XPath query. There is also support for some extensions, to be able
 * to query attribute values and text for example.
 */
class QueryTranslator {

    // This class tries to support the same characters in IDs and classes that
    // are supported by browsers/the specification. According to this source
    // (https://mathiasbynens.be/notes/html5-id-class) and personal testing, it
    // appears that nearly all characters are supported, although some need to
    // be escaped. This class does not parse escape sequences (it is assumed
    // the PHP parser already did this), but it does support escaping with a
    // backslash, e.g. '\#'  is interpreted as '#'. Note that when passing this
    // as a string to this class, one should use '\\#' in their source code.

    // The below is a number of constants used to keep track where we are while
    // parsing, what we parsed before, et cetera.
    const PARSE_ELEMENT =     0; // parsing a tagname
    const PARSE_CLASS =       1; // parsing a class
    const PARSE_ID =          2; // parsing an id
    const PARSE_DESCENDANT =  4; // parsing a descendant (whitespace in selector)
    const PARSE_CHILD =       8; // parsing a child (> in selector)
    const PARSE_LITERAL =    16; // parsing an escaped character
    const PARSE_ADDITIONAL = 32; // parsing an additional query (comma)
    const PARSE_XFUNC =      64; // parsing a CSS addition of siflawler

    /**
     * Translate a CSS selector to an XPath query.
     *
     * @param $css Selector to translate.
     */
    public static function cssToXPath($css) {
        // possibly remove scheme from selector
        if (substr($css, 0, 4) === 'css:') {
            $css = substr($css, 4);
        }

        // build a parse tree
        list($parse_stack, $parse_stack_values) = self::build_parse_tree($css);

        // start building an XPath query and return that
        return self::build_xpath_query($parse_stack, $parse_stack_values);
    }

    /**
     * Return if the given query/selector is more likely to be a CSS query than
     * an XPath query. This is determined using a heuristic. A query can start
     * with 'css:' or 'xpath:', which is considered as well. If that scheme is
     * present, then no heuristic is used, but the scheme is considered to be
     * true (as in, we believe what it says itself).
     *
     * @param $query Query or selector to consider.
     * @return A boolean indicating if @c $query is likely to be CSS (@c true)
     *         or more likely to be an XPath query (@c false).
     */
    public static function isCss($query) {
        if (substr($query, 0, 4) === 'css:') {
            return true;
        } else if (substr($query, 0, 6) === 'xpath:') {
            return false;
        }

        $matches_css = null;
        $matches_xpath = null;
        preg_match('/[.#>]/', $query, $matches_css);
        preg_match('/[\/:@=]/', $query, $matches_xpath);
        return (count($matches_css) >= count($matches_xpath));
    }

    /**
     * Translate query to an XPath query. This function checks for the presence
     * of a scheme (string starts with 'css:' or 'xpath:') and removes that in
     * the result. Also, it translates CSS selectors to XPath queries.
     *
     * @param $query The query to consider.
     * @return An XPath query that can be directly used for queries. Note that
     *         this may be equal to @c $query, if that was a query already.
     */
    public static function translateQuery($query) {
        // check for null
        if ($query === null) {
            return $query;
        }

        // is it CSS?
        if (self::isCss($query)) {
            return self::cssToXPath($query);
        }
        // we assume it is XPath now
        if (substr($query, 0, 6) === 'xpath:') {
            return substr($query, 6);
        }
        return $query;
    }


    /**
     * Add an 'and' selector to a query, if needed.
     */
    private static function add_and($i, $parse_stack, &$xpath) {
        if ($i > 0 && ($parse_stack[$i - 1] & (self::PARSE_CLASS |
                                               self::PARSE_ID)) > 0) {
            $xpath = substr($xpath, 0, -1) . ' and ';
        } else {
            $xpath .= '[';
        }
    }

    /**
     * Build a parse tree, given a CSS selector.
     */
    private static function build_parse_tree($css) {
        $css = trim($css);
        $css_len = strlen($css);
        $parse_stack = array(self::PARSE_ELEMENT);
        $parse_stack_values = array('');

        for ($char_index = 0; $char_index < $css_len; $char_index++) {
            $char = $css[$char_index];

            // was it an escaped character?
            if (end($parse_stack) === self::PARSE_LITERAL) {
                array_pop($parse_stack);
                $parse_stack_values[count($parse_stack_values) - 1] .= $char;
                continue;
            }

            // no? what are we parsing then? :)
            switch ($char) {
                case ',':
                    $parse_stack[] = self::PARSE_ADDITIONAL;
                    $parse_stack_values[] = null;
                    break;
                case '.':
                    $parse_stack[] = self::PARSE_CLASS;
                    $parse_stack_values[] = '';
                    break;
                case '#':
                    $parse_stack[] = self::PARSE_ID;
                    $parse_stack_values[] = '';
                    break;
                case '$':
                    $parse_stack[] = self::PARSE_XFUNC;
                    $parse_stack_values[] = '';
                    break;
                case '>':
                    if (end($parse_stack) === self::PARSE_DESCENDANT) {
                        $parse_stack[count($parse_stack) - 1] = self::PARSE_CHILD;
                    } else {
                        $parse_stack[] = self::PARSE_CHILD;
                        $parse_stack_values[] = null;
                    }
                    break;
                case ' ':  // a space
                case "\n": // linefeed
                case "\r": // carriage return
                case "\t": // tab
                case "\v": // vertical tab
                    if ((end($parse_stack) & (self::PARSE_DESCENDANT |
                                              self::PARSE_CHILD |
                                              self::PARSE_ADDITIONAL)) === 0) {
                        $parse_stack[] = self::PARSE_DESCENDANT;
                        $parse_stack_values[] = null;
                    }
                    break;
                case '\\':
                    $parse_stack[] = self::PARSE_LITERAL;
                    break;
                default: // all the rest is seen as a character that is part of
                         // a CSS identifier and appended to the latest value
                    // if we were looking for a child or descendant, here it is
                    if ((end($parse_stack) & (self::PARSE_CHILD |
                                             self::PARSE_DESCENDANT |
                                             self::PARSE_ADDITIONAL)) > 0) {
                        $parse_stack[] = self::PARSE_ELEMENT;
                        $parse_stack_values[] = '';
                    }
                    // append character to latest value
                    $parse_stack_values[count($parse_stack_values) - 1] .= $char;
                    break;
            }
        }

        // remove empty element if there was no element
        if ($parse_stack_values[0] === '') {
            array_shift($parse_stack);
            array_shift($parse_stack_values);
        }

        // return tree
        return array($parse_stack, $parse_stack_values);
    }

    /**
     * Given a parse tree as returned by \siflawler\QueryTranslator#build_parse_tree
     * (that is, both the tree and the values), return a corresponding XPath query.
     */
    private function build_xpath_query($parse_stack, $parse_stack_values) {
        $xpath = '//';
        for ($i = 0; $i < count($parse_stack); $i++) {
            // if a new 'subquery' starts, we possible need to add a *
            if (($parse_stack[$i] & (self::PARSE_ID |
                                     self::PARSE_CLASS)) > 0 &&
                    ($i > 0 && ($parse_stack[$i - 1] & (self::PARSE_DESCENDANT |
                        self::PARSE_CHILD | self::PARSE_ADDITIONAL)) > 0 ||
                     $i == 0)) {
                $xpath .= '*';
            }

            // add stuff to the XPath query depending on type of element
            switch ($parse_stack[$i]) {
                case self::PARSE_ELEMENT:
                    $xpath .= $parse_stack_values[$i];
                    break;
                case self::PARSE_CLASS:
                    // possibly add an 'and'
                    self::add_and($i, $parse_stack, $xpath);
                    // add selector query (inspired by http://css2xpath.appspot.com)
                    $xpath .= "(contains(concat(' ', normalize-space(@class), "
                        . "' '), ' " . $parse_stack_values[$i] . " '))]";
                    break;
                case self::PARSE_ID:
                    // possibly add an 'and'
                    self::add_and($i, $parse_stack, $xpath);
                    // add selector query
                    $xpath .= '@id="' . $parse_stack_values[$i] . '"]';
                    break;
                case self::PARSE_DESCENDANT:
                    $xpath .= '/descendant::';
                    break;
                case self::PARSE_CHILD:
                    $xpath .= '/';
                    break;
                case self::PARSE_ADDITIONAL:
                    $xpath .= ' | //';
                    break;
                case self::PARSE_XFUNC:
                    $matches = null;
                    if ($parse_stack_values[$i] === 'text()') {
                        $xpath .= '/text()';
                    } else if (preg_match('/attr\("([^\t\n\f \/>"\'=]+)"\)/',
                            $parse_stack_values[$i], $matches) === 1) {
                        // http://stackoverflow.com/a/926136/962603
                        $xpath .= '/@' . $matches[1];
                    }
                    break;
            }
        }
        return $xpath;
    }

}
