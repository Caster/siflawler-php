<?php

namespace siflawler;

use \siflawler\QueryTranslator;

/**
 * This class provides a static method to parse XML (or HTML) and find specific
 * data on the page. It can also search for the next page(s) to crawl.
 */
class Parser {

    /**
     * Parse a given HTML string and find data and next page(s) to parse.
     *
     * @param $options Options object. Used to read data and next selectors.
     * @param $page_url URL that page was retrieved from. Used for relative links.
     *            Can be an array of the same length as @c $page too.
     * @param $page Data to parse as a string. Can be an array of strings too.
     * @param $data Array of data found so far. Will be extended with data found
     *            on this page (or these pages), if any is found.
     * @return A string or array of strings of URLs to visit next.
     */
    public static function find($options, $page_url, $page, &$data) {
        $docs = array();
        $xpaths = array();
        $page_urls = (is_array($page_url) ? $page_url : array($page_url));
        $pages = (is_array($page) ? $page : array($page));
        $page_count = count($pages);

        // parse the page(s)
        for ($i = 0; $i < $page_count; $i++) {
            list($doc, $xpath) = self::load_html($pages[$i]);
            $docs[] = $doc;
            $xpaths[] = $xpath;
        }

        // search for data
        $data_keys = $options->get('get');
        $find_query = $options->get('find');
        for ($i = 0; $i < $page_count; $i++) {
            $data_nodes = @$xpaths[$i]->query(
                QueryTranslator::translateQuery($find_query));
            if ($data_nodes === false) {
                // invalid query!
                if ($options->get('warnings')) {
                    echo("[W] siflawler: malformed (translated) query for 'find'!\n");
                    echo("    original query:   '${find_query}'\n");
                    echo("    translated query: '" . QueryTranslator::translateQuery($find_query) . "'\n");
                }
                return null;
            }
            if ($data_nodes->length === 0 && $options->get('warnings')) {
                echo("[W] siflawler: no interesting nodes found on page '{$page_urls[$i]}'.\n");
            }
            foreach ($data_nodes as $data_node) {
                $data_point = new \stdClass();
                foreach ($data_keys as $key => $query) {
                    $result = @$xpaths[$i]->query('.' . // make the XPath query relative to context
                        QueryTranslator::translateQuery($query), $data_node);
                    if ($result === false && $options->get('warnings')) {
                        echo("[W] siflawler: malformed (translated) query for 'get['$key']'!\n");
                        echo("    original query:   '${query}'\n");
                        echo("    translated query: '" . QueryTranslator::translateQuery($query) . "'\n");
                    }
                    if ($result !== false) {
                        if ($result->length === 1) {
                            $data_point->{$key} = self::get_node_value($result->item(0));
                        } else {
                            $data_point->{$key} = array();
                            for ($j = 0; $j < $result->length; $j++) {
                                $data_point->{$key}[] = self::get_node_value($result->item($j));
                            }
                        }
                    }
                }
                $data[] = $data_point;
                unset($data_point);
            }
        }

        // find the next element(s)
        $next_urls = array();
        $next_query = $options->get('next');
        $echo_warnings = $options->get('warnings');
        if ($next_query !== null) {
            for ($i = 0; $i < $page_count; $i++) {
                $next_nodes = $xpaths[$i]->query(
                    QueryTranslator::translateQuery($next_query));
                if ($next_nodes !== false && $next_nodes->length > 0) {
                    $next = array();
                    foreach ($next_nodes as $next_node) {
                        $value = self::get_node_value($next_node);
                        // we only handle values that could be URLs, that is,
                        // strings... otherwise we ignore the value
                        if (is_string($value)) {
                            $next[] = Fetcher::check_url($page_urls[$i], $value);
                        } else if ($echo_warnings) {
                            printf('Ignored a "next" element that did not '
                                 .'result in a text value.%s', PHP_EOL);
                        }
                    }
                    if (count($next) !== 0) {
                        // push all elements in the $next array on $next_urls
                        // do it this way because unshifting a reference is not
                        // possible anymore (counts as call-time pass-by-ref)
                        array_unshift($next, null);
                        $next[0] = &$next_urls;
                        call_user_func_array('array_push', $next);
                    }
                }
            }
        }

        // return what we found
        return (count($next_urls) === 0 ? null :
                (count($next_urls) === 1 ? $next_urls[0] : $next_urls));
    }

    /**
     * Return the value of a node. This can be the text of a \DOMText node, the
     * value of a \DOMAttr node, et cetera. If the given node is "unknown" in
     * that regard, then the node itself is returned.
     */
    public static function get_node_value($node) {
        switch (get_class($node)) {
            case 'DOMAttr':
                return $node->value;
            case 'DOMElement':
                return '<' . $node->tagName . '>';
            case 'DOMText':
                return $node->wholeText;
            default:
                return $node;
        }
    }

    /**
     * Load an HTML string into a \DOMDocument and create a \DOMXPath on it.
     *
     * @param $page HTML string to load.
     * @return An array with a \DOMDocument and \DOMXPath object.
     */
    public static function load_html($page) {
        $prev_value = libxml_use_internal_errors(true);
        $doc = \DOMDocument::loadHTML($page, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG
            | LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING | LIBXML_PARSEHUGE);
        libxml_clear_errors();
        libxml_use_internal_errors($prev_value);
        $xpath = new \DOMXPath($doc);
        return array($doc, $xpath);
    }

}
