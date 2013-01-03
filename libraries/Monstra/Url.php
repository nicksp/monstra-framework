<?php

namespace Monstra;

class Url
{

    /**
     * Protected constructor since this is a static class.
     *
     * @access  protected
     */
    protected function __construct()
    {
        // Nothing here
    }

    /**
     * Returns the base URL of the application.
     *
     * <code>
     *     echo URL::base();
     * </code>
     *
     * @access  public
     * @return string
     */
    public static function base()
    {

        static $base = false;

        if ($base === false) {

            $base = Config::get('monstra.base_url');

            // Try to autodetect base url if its not configured
            if ($base === '' && isset($_SERVER['HTTP_HOST'])) {

                $protocol = Request::isSecure() ? 'https' : 'http';

                $script = $_SERVER['SCRIPT_NAME'];

                $base = rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . str_replace(basename($script), '', $script), '/');
            }

            // Add index.php?
            !Config::get('monstra.clean_urls') && $base .= '/index.php';
        }

        return $base;
    }

    /**
     * Returns a monstra framework URL.
     *
     * <code>
     *     // Will print http://example.org/foo/bar
     *     echo URL::to('foo/bar');
     *
     *     // Will print http://example.org/foo/bar?key=value&key2=value2
     *     echo URL::to('foo/bar', array('key1' => 'value1', 'key2' => 'value2'));
     * </code>
     *
     * @access  public
     * @param   string   URL segments
     * @param   array    (optional) Associative array used to build URL-encoded query string
     * @param   string   (optional) Argument separator
     * @return string
     */
    public static function to($route = '', array $params = array(), $separator = '&amp;')
    {
        $url = static::base() . '/' . $route;

        if ( ! empty($params)) {
            $url .= '?' . http_build_query($params, '', $separator);
        }

        return $url;
    }

    /**
     * Returns the current URL of the main request.
     *
     * <code>
     *     echo URL::current();
     * </code>
     *
     * @access  public
     * @param   array    (optional) Associative array used to build URL-encoded query string
     * @param   string   (optional) Argument separator
     * @return string
     */
    public static function current(array $params = array(), $separator = '&amp;')
    {
        return static::to(Request::route(), $params, $separator);
    }
}
