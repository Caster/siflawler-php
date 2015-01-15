<?php

namespace siflawler;

use \siflawler\Config\Options;
use \siflawler\Fetcher;
use \siflawler\Parser;
use \siflawler\Exceptions\ConfigException;
use \siflawler\Exceptions\NotFoundException;

/**
 * Main class of the siflawler library.
 */
class Crawler {

    /**
     * Options dictionary, used to read settings from.
     */
    private $_options;

    /**
     * Construct a new \siflawler\Crawler that reads settings from the given
     * file, in JSON format.
     *
     * @param $configFile Path to a file with configuration in JSON format.
     */
    public function __construct($configFile) {
        $this->_options = new Options($configFile);
    }

    /**
     * Start crawling.
     */
    public function crawl() {
        // intialise some variables
        $count = 0;
        $requests_left = $this->_options->get('max_requests');
        $requests_limit = ($requests_left > 0);
        $verbose = $this->_options->get('verbose');
        $next = $this->_options->get('start');
        $data = array();

        // start crawling
        do {
            // check to see if we can do the following request
            if ($requests_limit) {
                // we will do a request per element of $next
                if (is_array($next)) {
                    $requests_left -= count($next);
                } else {
                    $requests_left--;
                }
                // can we do that?
                if ($requests_left < 0) {
                    if ($verbose) {
                        printf("Reached request limit of %d requests.\n",
                            $this->_options->get('max_requests'));
                    }
                    break;
                }
            }
            // load the next page, let user know if wanted
            if ($verbose) {
                printf("[%3d] Loading \"%s\"...\n", ++$count, $next);
            }
            try {
                $page = Fetcher::load($this->_options, $next);
            } catch (NotFoundException $nfe) {
                if ($this->_options->get('warnings')) {
                    printf("%s\n", $nfe->getMessage());
                }
                break;
            }
            // parse page and find data user wants and the next page(s) to crawl
            $next = Parser::find($this->_options, $page, $data);
        } while ($next !== null);

        // return found data
        return $data;
    }

}
