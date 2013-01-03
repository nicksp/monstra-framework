<?php

namespace Monstra;

class Autoloader
{
    /**
     * Mapping from class names to paths.
     *
     * @var array
     */
    protected static $classes = array();

    /**
     * PSR-0 directories.
     *
     * @var array
     */
    protected static $psr = array(
        MONSTRA_LIBRARIES_PATH
    );

    /**
     * Class aliases.
     *
     * @var array
     */
    protected static $aliases = array();

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
     * Add class to mapping.
     *
     * <code>
     *     ClassLoader::addClasses('monstra\ClassName', MONSTRA_LIBRARIES_PATH . '/monstra/ClassName.php');
     * </code>
     *
     * @access  public
     * @param string $class_name Class name
     * @param string $class_path Full path to class
     */
    public static function addClass($class_name, $class_path)
    {
        static::$classes[$class_name] = $class_path;
    }

    /**
     * Add multiple classes to mapping.
     *
     * <code>
     *     ClassLoader::addClasses(array(
     *         'monstra\ClassName' => MONSTRA_LIBRARIES_PATH . '/monstra/ClassName.php',
     *     ));
     * </code>
     *
     * @access  public
     * @param array $classes Array of classes to map (key = class name and value = class path)
     */
    public static function addClasses(array $classes)
    {
        foreach ($classes as $name => $path) {
            static::$classes[$name] = $path;
        }
    }

    /**
     * Adds a PSR-0 directory path.
     *
     * <code>
     *     ClassLoader::directory('/path/to/library');
     * </code>
     *
     * @access  public
     * @param string $path Path to PSR-0 directory
     */
    public static function directory($path)
    {
        static::$psr[] = $path;
    }

    /**
     * Set an alias for a class.
     *
     * <code>
     *     ClassLoader::alias('monstra\V', 'monstra\View');
     * </code>
     *
     * @access  public
     * @param string $alias      Class alias
     * @param string $class_name Class name
     */
    public static function alias($alias, $class_name)
    {
        static::$aliases[$alias] = $class_name;
    }

    /**
     * Autoloader.
     *
     * <code>
     *     ClassLoader::load();
     * </code>
     *
     * @access  public
     * @param  string  $class_name Class name
     * @return boolean
     */
    public static function load($class_name)
    {

        $class_name = ltrim($class_name, '\\');

        // Try to autoload an aliased class
        if (isset(static::$aliases[$class_name])) {
            class_alias(static::$aliases[$class_name], $class_name);
        }

        // Try to load a mapped class
        if (isset(static::$classes[$class_name]) && file_exists(static::$classes[$class_name])) {
            include static::$classes[$class_name];

            return true;
        }

        // Try to load an application class
        $filename = MONSTRA_APPLICATION_PATH . '/' . str_replace('\\', '/', $class_name) . '.php';
        if (file_exists($filename)) {
            include $filename;

            return true;
        }

        // Try to load class from a PSR-0 compatible library

        $filename  = '';
        $namespace = '';

        if ($lastNsPos = strripos($class_name, '\\')) {
            $namespace = substr($class_name, 0, $lastNsPos);
            $class_name = substr($class_name, $lastNsPos + 1);
            $filename  = str_replace('\\', '/', $namespace) . '/';
        }

        $filename .= str_replace('_', '/', $class_name) . '.php';

        foreach (static::$psr as $path) {
            if (file_exists($path . '/' . $filename)) {
                include($path . '/' . $filename);

                return true;
            }
        }

        return false;
    }
}
