<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license MIT
 */

if (!function_exists('url')) {
    /**
     * Construct a url on the current site.
     *
     * @param string $path The path of the url.
     * @param mixed $domain Whether or not to include the domain. This can be one of the following.
     * - false: The domain will not be included.
     * - true: The domain will be included.
     * - //: A schemeless domain will be included.
     * - /: Just the path will be returned.
     * @return string Returns the url.
     */
    function url($path, $domain = false) {
        if (is_url($path)) {
            return $path;
        }

        return Garden\Request::current()->makeUrl($path, $domain);
    }
}

function url_local($url = false)
{
    $request = Garden\Request::current();
    if(!$url) {
        $url = $request->getUrl();
    }
    
    $host = $request->getHost();
    $url = parse_url($url);

    if($host != val('host', $url))
        return false;

    $path = val('path', $url);
    $query = val('query', $url);
    $url = '/'.ltrim($path.($query ? '?'.$query : null), '/');

    return $url;
}

/**
 * Whether or not a string is a url in the form http://, https://, or //.
 *
 * @param string $str The string to check.
 * @return bool
 *
 * @category String Functions
 * @category Internet Functions
 */
function is_url($str) {
    if (!$str) {
        return false;
    }
    if (substr($str, 0, 2) == '//') {
        return true;
    }
    if (strpos($str, '://', 1) !== false) {
        return true;
    }
    return false;
}

function is_local_url($url)
{
    return !preg_match('#^(http|\/\/)#', $url);
}


if (!function_exists('is_id')) {
    /**
     * Finds whether the type given variable is a database id.
     *
     * @param mixed $val The variable being evaluated.
     * @param bool $allow_slugs Whether or not slugs are allowed in the url.
     * @return bool Returns `true` if the variable is a database id or `false` if it isn't.
     */
    function is_id($val, $allow_slugs = false) {
        if (is_numeric($val)) {
            return true;
        } elseif ($allow_slugs && preg_match(`^\d+-`, $val)) {
            return true;
        } else {
            return false;
        }
    }
}