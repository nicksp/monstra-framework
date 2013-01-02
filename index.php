<?php


/**
 * Define the name of your application. The name must match the name of the app directory.
 */
define('MONSTRA_APPLICATION_NAME', 'application');

/**
 * Define the path to the parent directory of the app directory (without trailing slash).
 */
define('MONSTRA_APPLICATION_PATH', __DIR__);

/**
 * Define aplication directory
 */
define('MONSTRA_APPLICATION', MONSTRA_APPLICATION_PATH . '/' . MONSTRA_APPLICATION_NAME);

/**
 * Define the path to the libraries directory (without trailing slash).
 */
define('MONSTRA_LIBRARIES_PATH', MONSTRA_APPLICATION_PATH . '/libraries');

/**
 * Init Monstra
 */
require MONSTRA_LIBRARIES_PATH . '/monstra/_init.php';
