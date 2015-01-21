<?php

namespace siflawler\Data;

/**
 * This class implements a cache that can keep track of URLs that were already
 * visited. It can filter these URLs out of lists of URLs to visit next.
 */
class Cache {

    /**
     * Array with visited URLs (or strings, really, they do not have to be URLs).
     * This array should at all times be sorted.
     */
    private $_cache;

    public function __construct() {
        $this->_cache = array();
    }

    /**
     * Filter the given list of URLs so that it contains only URLs not in this
     * cache yet. This function changes its parameter, it does not return a value.
     *
     * @param $urls Array of URLs to filter, or a single URL as a string.
     */
    public function filter(&$urls) {
        if (is_array($urls)) {
            // remove elements we have in cache already, add others to it
            for ($i = count($urls) - 1; $i >= 0; $i--) {
                if ($this->cache_contains($urls[$i])) {
                    array_splice($urls, $i, 1);
                }
            }
            // deal with special cases now
            if (count($urls) === 1) {
                $urls = $urls[0];
            } else if (count($urls) === 0) {
                $urls = null;
            }
        } else if (is_string($urls)) {
            if ($this->cache_contains($urls)) {
                $urls = null;
            }
        }
    }

    /**
     * Return the current contents of the cache.
     */
    public function get_contents() {
        return $this->_cache;
    }


    /**
     * Do a binary search in the given array, returning the greatest element
     * that is smaller than or equal to the given one, according to the given
     * comparator function.
     * 
     * @param $haystack Array to search in.
     * @param $needle Element to look for, or the greatest element smaller than
     *            this one if this element itself is not in the array.
     * @param $comparator Comparator function to compare elements in the array.
     * @return The index of the @c $needle element, or if that element could
     *         not be found, the greatest element still smaller than that.
     *         If no such element exists either then @c -1 is returned.
     */
    private function binary_search_lt($haystack, $needle, $comparator) {
        // http://en.wikipedia.org/wiki/Binary_search_algorithm#Deferred_detection_of_equality
        $imin = 0;
        $imax = count($haystack) - 1;

        while ($imin < $imax) {
            $imid = $imin + (int) (($imax - $imin) / 2);

            if (call_user_func_array($comparator,
                    array($haystack[$imid], $needle)) < 0) {
                $imin = $imid + 1;
            } else {
                $imax = $imid;
            }
        }

        if ($imin == $imax) {
            if (call_user_func_array($comparator,
                    array($haystack[$imin], $needle)) <= 0) {
                return $imin;
            }
            if ($imin > 0 && call_user_func_array($comparator,
                    array($haystack[$imin - 1], $needle)) <= 0) {
                return $imin - 1;
            }
        }
        return -1;
    }

    /**
     * Check if a URL is in the cache and if not, add it to the cache.
     *
     * @param $url URL to check for.
     * @return Boolean indicating if the URL was in the cache. If it was not,
     *         then it will be added, so a subsequent call to this function with
     *         the same parameter will return a different result in that case!
     */
    private function cache_contains($url) {
        $index = $this->binary_search_lt($this->_cache, $url, 'strcmp');
        $contains = ($index >= 0 && $this->_cache[$index] === $url);
        if (!$contains) {
            // insert element in the cache at the location it should be
            array_splice($this->_cache, ($index < 0 ? 0 : $index + 1), 0, $url);
        }
        return $contains;
    }

}
