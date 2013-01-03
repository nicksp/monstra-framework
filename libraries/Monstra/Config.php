<?php

namespace Monstra;

use RuntimeException;

class Config
{
    /**
     * Config array.
     *
     * @var array
     */
    protected static $config;

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
     * Returns config value or entire config array from a file.
     *
     * <code>
     *     $monstra = Config::get('monstra');
     *     $charset = Config::get('monstra.charset');
     * </code>
     *
     * @access  public
     * @param   string  Config key
     * @param   mixed   (optional) Default value to return if config value doesn't exist
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        //var_dump(static::$config);

        $keys = explode('.', $key, 2);

        if ( ! isset(static::$config[$keys[0]])) {
            $path = Monstra::path('config', $keys[0]);

            if (file_exists($path) === false) {
                throw new ConfigException(vsprintf("%s(): The '%s' config file does not exist.", array(__METHOD__, $keys[0])));
            }

            static::$config[$keys[0]] = include($path);
        }

        if ( ! isset($keys[1])) {
            return static::$config[$keys[0]];
        } else {
            return Arr::get(static::$config[$keys[0]], $keys[1], $default);
        }
    }

}

class ConfigException extends RuntimeException{}
