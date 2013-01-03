<?php

/**
 *  Monstra requires PHP 5.3.0 or greater
 */
if (version_compare(PHP_VERSION, "5.3.0", "<")) exit("Monstra requires PHP 5.3.0 or greater.");

/**
 * Tik-tak
 */
define('MONSTRA_START', microtime(true));

/**
 * Map all core classes
 */
include MONSTRA_LIBRARIES_PATH . '/Monstra/Autoloader.php';
Monstra\Autoloader::addClasses(array(
    'Monstra\Config'     => MONSTRA_LIBRARIES_PATH . '/Monstra/Config.php',
    'Monstra\Controller' => MONSTRA_LIBRARIES_PATH . '/Monstra/Controller.php',
    'Monstra\Model'      => MONSTRA_LIBRARIES_PATH . '/Monstra/Model.php',
    'Monstra\Monstra'    => MONSTRA_LIBRARIES_PATH . '/Monstra/Monstra.php',
    'Monstra\View'       => MONSTRA_LIBRARIES_PATH . '/Monstra/View.php',
    'Monstra\Response'   => MONSTRA_LIBRARIES_PATH . '/Monstra/Response.php',
    'Monstra\Request'    => MONSTRA_LIBRARIES_PATH . '/Monstra/Request.php',
    'Monstra\Url'        => MONSTRA_LIBRARIES_PATH . '/Monstra/Url.php',

));

/**
 * Set up autoloader
 */
spl_autoload_register('Monstra\Autoloader::load');

/**
 * Set core environment
 *
 * Monstra has four predefined environments:
 *   monstra\Monstra::DEVELOPMENT - The development environment.
 *   monstra\Monstra::TESTING     - The test environment.
 *   monstra\Monstra::STAGING     - The staging environment.
 *   monstra\Monstra::PRODUCTION  - The production environment.
 */
Monstra\Monstra::$environment = Monstra\Monstra::DEVELOPMENT;

/**
 * Monstra Error Reporting
 */
if (Monstra\Monstra::$environment == Monstra\Monstra::DEVELOPMENT) {

    /**
     * Report All Errors
     *
     * By setting error reporting to -1, we essentially force PHP to report
     * every error, and this is guranteed to show every error on future
     * releases of PHP. This allows everything to be fixed early!
     */
    error_reporting(-1);

} else {

    /**
     * Production environment
     */
    error_reporting(0);
}

/**
 * Run Monstra Run :)
 */
Monstra\Monstra::run();
