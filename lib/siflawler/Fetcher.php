<?php

namespace siflawler;

use \siflawler\Exceptions\NotFoundException;

/**
 * Class that can load external files using cURL.
 */
class Fetcher {

    /**
     * Returns the given URL if it is valid. If it appears to be an absolute
     * URL then it is prefixed with the base URL of the page URL to make it a
     * full URL that can be used by cURL.
     * If it does not appear to be an absolute URL, @c null is returned.
     *
     * @param $page_url URL where page was retrieved from. Used to make links
     *            absolute if they are not.
     * @param $url A URL that was found on the page retrieved through above URL.
     */
    public static function check_url($page_url, $url) {
        $url_info = parse_url($url);
        if (isset($url_info['scheme']) && isset($url_info['host'])) {
            return $url;
        }

        // is it not an absolute URL?
        if (strlen($url) > 0 && $url[0] !== '/' && $url[0] !== '#') {
            return null;
        }

        // is it a hash?
        if ($url[0] === '#') {
            return $page_url . $url;
        }

        // try to parse the page URL
        $url_info = parse_url($page_url);
        if (isset($url_info['host'])) {
            // try to use the same scheme as the page URL
            $scheme = 'http';
            if (isset($url_info['scheme'])) {
                $scheme = $url_info['scheme'];
            }
            return sprintf('%s://%s%s', $scheme, $url_info['host'], $url);
        }

        // well, the page URL does not seem to be a valid URL...
        return null;
    }

    /**
     * Load a document from a URL.
     *
     * @param $options Options object. Used to read timeout.
     * @param $url URL to load a document from. Can be an array of URLs too.
     * @return Data read from given URL. Can be an array of data if an array of
     *         URLs was passed in @c $url.
     * @throw NotFoundException If a URL returned a HTTP 404 code.
     */
    public static function load($options, $url) {
        $curl_handles = array();
        $url_count = (is_array($url) ? count($url) : 1);
        $multi = ($url_count > 1);
        $mh = ($multi ? curl_multi_init() : null);

        // initialise cURL handle(s)
        for ($i = 0; $i < $url_count; $i++) {
            $ch = curl_init();
            self::set_curl_options($options, $ch, ($multi ? $url[$i] : $url));
            $curl_handles[] = $ch;
            if ($multi) {
                curl_multi_add_handle($mh, $ch);
            }
        }

        // execute cURL handle(s) and check data
        if ($multi) {
            // execute connections in parallel
            $active = 0;
            do {
                curl_multi_exec($mh, $active);
                curl_multi_select($mh);
            } while ($active > 0);
            // check HTTP codes and retrieve data
            $data = array();
            for ($i = 0; $i < $url_count; $i++) {
                self::check_curl_http_code($curl_handles[$i], $url[$i]);
                $data[] = curl_multi_getcontent($curl_handles[$i]);
                // remove handle from multi handle and close it
                curl_multi_remove_handle($mh, $curl_handles[$i]);
                curl_close($curl_handles[$i]);
            }
            // close multi handle
            curl_multi_close($mh);
        } else {
            $data = curl_exec($curl_handles[0]);
            self::check_curl_http_code($curl_handles[0], $url);
            curl_close($curl_handles[0]);
        }

        // return data
        return $data;
    }


    /**
     * Check if the given executed cURL handle had a good response. Throw an
     * exception if that is not the case.
     *
     * @param $ch cURL handle to check.
     * @param $url URL that was addressed. Will be put in the exception message.
     */
    private static function check_curl_http_code($ch, $url) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code === 404) {
            throw new NotFoundException('Could not load "' . $url . '", got '
                . 'an HTTP 404 code.');
        } else if ($http_code === 0) {
            throw new NotFoundException('Could not load "' . $url . '", got '
                . 'no response. Is the URL valid?');
        }
    }

    /**
     * Set some cURL options on a cURL handle.
     *
     * @param $options Options object. Used to read timeout.
     * @param $ch cURL handle to set options on.
     * @param $url URL to set on the handle.
     */
    private static function set_curl_options($options, $ch, $url) {
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options->get('timeout'));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
    }

}
