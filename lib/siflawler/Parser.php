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
     * @param $page Data to parse as a string.
     * @param $data Array of data found so far. Will be extended with data found
     *            on this page, if any is found.
     * @return A string or array of strings of URLs to visit next.
     */
    public static function find($options, $page, &$data) {
        // TODO: support multiple pages if $page is an array
        // parse the page
        $prev_value = libxml_use_internal_errors(true);
        $doc = \DOMDocument::loadHTML($page, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG
            | LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING | LIBXML_PARSEHUGE);
        libxml_clear_errors();
        libxml_use_internal_errors($prev_value);
        $xpath = new \DOMXPath($doc);

        // search for data
        $data_keys = $options->get('get');
        $data_nodes = $xpath->query($options->get('find'));
        foreach ($data_nodes as $data_node) {
            $data_point = new \stdClass();
            foreach ($data_keys as $key => $query) {
                $result = $xpath->query($query, $data_node);
                if ($result !== false && $result->length > 0) {
                    $data_point->{$key} = self::get_node_value($result->item(0));
                }
            }
            $data[] = $data_point;
            unset($data_point);
        }

        // find the next element(s)
        $next_query = $options->get('next');
        if ($next_query !== null) {
            $next_nodes = $xpath->query($next_query);
            if ($next_nodes !== false && $next_nodes->length > 0) {
                $next = array();
                foreach ($next_nodes as $next_node) {
                    $value = self::get_node_value($next_node);
                    // we only handle values that could be URLs, that is, strings
                    // otherwise we ignore the value
                    if (is_string($value)) {
                        $next[] = Fetcher::check_url($options, $value);
                    }
                }
                if (count($next) === 0) {
                    return null;
                }
                if (count($next) === 1) {
                    return $next[0];
                }
                return $next;
            }
        }

        // we did not find the next element, so return that we should stop
        return null;
    }

    /**
     * Return the value of a node. This can be the text of a \DOMText node, the
     * value of a \DOMAttr node, et cetera. If the given node is "unknown" in
     * that regard, then the node itself is returned.
     */
    private static function get_node_value($node) {
        switch (get_class($node)) {
            case 'DOMAttr':
                return $node->value;
            case 'DOMText':
                return $node->wholeText;
            default:
                return $node;
        }
    }

}
