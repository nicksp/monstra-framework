<?php

namespace Monstra;

class Arr
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
     * Returns value from array using "dot notation".
     *
     * @access  public
     * @param   array   Array we're going to search
     * @param   string  Array path
     * @param   mixed   Default return value
     * @return mixed
     */
    public static function get(array $array, $path, $default = null)
    {

        $segments = explode('.', $path);

        foreach ($segments as $segment) {

            if ( ! is_array($array) || !isset($array[$segment])) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }


    /**
     * Sets an array value using "dot notation".
     *
     * @access  public
     * @param   array    Array you want to modify
     * @param   string   Array path
     * @param   mixed    Value to set
     */
    public static function set(array & $array, $path, $value)
    {

        $segments = explode('.', $path);

        while (count($segments) > 1) {

            $segment = array_shift($segments);

            if ( ! isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = array();
            }

            $array =& $array[$segment];
        }

        $array[array_shift($segments)] = $value;
    }


    /**
     * Deletes an array value using "dot notation".
     *
     * @access  public
     * @param   array    Array you want to modify
     * @param   string   Array path
     */
    public static function delete(array & $array, $path)
    {

        $segments = explode('.', $path);

        while (count($segments) > 1) {

            $segment = array_shift($segments);

            if ( ! isset($array[$segment]) || ! is_array($array[$segment])) {
                return false;
            }

            $array =& $array[$segment];
        }

        unset($array[array_shift($segments)]);

        return true;
    }

    /**
     * Returns a random value from an array.
     *
     * @access  public
     * @param   array   Array you want to pick a random value from
     * @return mixed
     */
    public static function random(array $array)
    {
        return $array[array_rand($array)];
    }
}
