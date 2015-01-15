<?php

namespace siflawler;

use \siflawler\Exceptions\NotFoundException;

/**
 * Class that can load external files using CURL.
 */
class Fetcher {

    /**
     * Returns the given URL if it is valid. If it appears to be an absolute
     * URL then it is prefixed with the 'start' option URL to make it a full
     * URL that can be used by CURL.
     * If it does not appear to be an absolute URL, @c null is returned.
     */
    public static function check_url($options, $url) {
        $url_info = parse_url($url);
        if (isset($url_info['scheme']) && isset($url_info['host'])) {
            return $url;
        }

        // is it not an absolute URL?
        if (strlen($url) > 0 && $url[0] !== '/') {
            return null;
        }

        // try to parse the 'start' url
        $url_info = parse_url($options->get('start'));
        if (isset($url_info['host'])) {
            // try to use the same scheme as the 'start' url
            $scheme = 'http';
            if (isset($url_info['scheme'])) {
                $scheme = $url_info['scheme'];
            }
            return sprintf('%s://%s%s', $scheme, $url_info['host'], $url);
        }

        // well, the 'start' url does not seem to be a valid URL...
        return null;
    }

    /**
     * Load a document from a URL.
     *
     * @param $options Options object.
     * @param $url URL to load a document from.
     * @return Data read from given URL.
     * @throw NotFoundException If the URL returned a HTTP 404 code.
     */
    public static function load($options, $url) {
        // TODO: support multiple loads if $url is an array
        //       return an array of data in that case
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options->get('timeout'));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code === 404) {
            throw new NotFoundException('Could not load "' . $url . '", got '
                . 'an HTTP 404 code.');
        } else if ($http_code === 0) {
            throw new NotFoundException('Could not load "' . $url . '", got '
                . 'no response. Is the URL valid?');
        }
        return $data;
    }

}
