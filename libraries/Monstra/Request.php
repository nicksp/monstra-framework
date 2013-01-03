<?php

namespace Monstra;

use ReflectionClass;
use RuntimeException;

class Request
{

    /**
     * Holds the route passed to the constructor.
     *
     * @var string
     */
    protected $route;

    /**
     * Holds the route to the main request.
     *
     * @var string
     */
    protected static $main_route;

    /**
     * Default route.
     *
     * @var string
     */
    protected $default_route;

    /**
     * Custom routes.
     *
     * @var array
     */
    protected $custom_routes;

    /**
     * Is this the main request?
     *
     * @var array
     */
    protected $is_main = true;

    /**
     * Ip address of the cilent that made the request.
     *
     * @var string
     */
    protected static $ip = '127.0.0.1';

    /**
     * From where did the request originate?
     *
     * @var string
     */
    protected static $referer;

    /**
     * Which request method was used?
     *
     * @var string
     */
    protected static $method;

    /**
     * Is this an Ajax request?
     *
     * @var boolean
     */
    protected static $is_ajax;

    /**
     * Was the request made using HTTPS?
     *
     * @var boolean
     */
    protected static $secure;

    /**
     * Array holding the arguments of the action method.
     *
     * @var array
     */
    protected $action_args;

    /**
     * Name of the controller.
     *
     * @var string
     */
    protected $controller;

    /**
     * Name of the action.
     *
     * @var string
     */
    protected $action;

    /**
     * Namespace of the controller class.
      *
     * @var string
     */
    protected $namespace;

    /**
    * Constructor.
    *
    * @access  public
    * @param   string $route (optional) URL segments. Default is null
    */
    public function __construct($route = null)
    {

        // Set route
        $this->route = $route;

        // Get routes
        $config = Config::get('routes');

        // Set default and custom routes
        $this->default_route = $config['default_route'];
        $this->custom_routes = $config['custom_routes'];

        $this->namespace = '\\' . MONSTRA_APPLICATION_NAME . '\controllers\\';

        static $mainRequest = true;

        if ($mainRequest === true) {

            // Get the ip of the client that made the request
            if ( ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip = array_pop($ip);
            } elseif ( ! empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif ( ! empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            } elseif ( ! empty($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            if (isset($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                static::$ip = $ip;
            }

            // From where did the request originate?
            static::$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

            // Which request method was used?
            static::$method = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) :
                              (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');

            // Is this an Ajax request?
            static::$is_ajax = (bool) (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));

            // Was the request made using HTTPS?
            static::$secure = (!empty($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN)) ? true : false;
        } else {
            $this->is_main = false;
        }

        $mainRequest = false; // Subsequent requests will be treated as subrequests
    }

    /**
     * Factory method making method chaining possible right off the bat.
     *
     * <code>
     *     $request = Request::factory('welcome/index');
     * </code>
     *
     * @access  public
     * @param  string          $route (optional) URL segments. Default is null
     * @return monstra\Request
     */
    public static function factory($route = null)
    {
        return new static($route);
    }

    /**
     * Routes the request to the appropriate controller action.
     *
     * @access  protected
     * @return boolean
     */
    protected function router()
    {

        // Set root path
        $controller_path = $controllerRootPath = MONSTRA_APPLICATION . '/controllers/';

        // Get the route
        $route = '';

        if ($this->route !== null) {
            $route = $this->route;
        } elseif (isset($_SERVER['PATH_INFO']) && $this->is_main()) {
            $route = $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['PHP_SELF']) && $this->is_main()) {
            $route = mb_substr($_SERVER['PHP_SELF'], mb_strlen($_SERVER['SCRIPT_NAME']));
        }

        $route = trim($route, '/');

        if ($this->is_main()) {
            static::$main_route = $route;
        }

        if ($route === '') {
            $route = trim($this->default_route, '/');
        }

        // Remap custom routes
        if (count($this->custom_routes) > 0) {

            foreach ($this->custom_routes as $pattern => $real_route) {

                // Compile a route!
                $pattern = str_replace(array('(:any)', '(:alnum)', '(:num)', '(:alpha)', '(:segment)'),
                                       array('(.+)', '([[:alnum:]]+)', '([[:digit:]]+)', '([[:alpha:]]+)', '([^/]*)'), $pattern);

                if (preg_match('#^' . $pattern . '$#iu', $route) === 1) {

                    if (strpos($real_route, '$') !== false) {
                        $real_route = preg_replace('#^' . $pattern . '$#iu', $real_route, $route);
                    }

                    $route = trim($real_route, '/');

                    break;
                }
            }
        }

        // Get the URL segments
        $segments = explode('/', $route, 100);

        // Route the request
        foreach ($segments as $segment) {

            $path = $controller_path . $segment;

            if (is_dir($path)) {

                // Just a directory - Jump to next iteration
                $controller_path  .= $segment . '/';

                $this->namespace .= $segment . '\\';

                array_shift($segments);

                continue;

            } elseif (is_file($path . '.php')) {

                // We have found our controller - Exit loop
                $this->controller = $segment;

                array_shift($segments);

                break;

            } else {

                // No directory or controller - Stop routing
                return false;
            }
        }

        if (empty($this->controller)) {
            $this->controller = 'index'; // default controller
        }

        // Get the action we want to execute
        $this->action = array_shift($segments);

        if ($this->action === null) {
            $this->action = 'index';
        }

        // Remaining segments are passed as parameters to the action
        $this->action_args = $segments;

        // Check if file exists
        if (file_exists($controller_path . $this->controller . '.php') === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Executes the controller and action found by the route method.
     *
     * <code>
     *     $response = Request::factory('controller/action')->execute();
     * </code>
     *
     * @access  public
     * @return monstra\Response
     */
    public function execute()
    {
        // Route request
        if ($this->router() === false) {
            throw new RequestException(404);
        }

        // Validate controller class
        $controller_class = new ReflectionClass($this->namespace . $this->controller);

        // Check if controller extends \Monstra\Controller ?
        if ($controller_class->isSubClassOf('\Monstra\Controller') === false) {
            throw new RequestException(vsprintf("%s(): The controller class needs to be a subclass of Monstra\Controller.", array(__METHOD__)));
        }

        // Check if class is abstract
        if ($controller_class->isAbstract()) {
            throw new RequestException(404);
        }

        // Instantiate controller
        $response = new Response();

        $controller = $controller_class->newInstance($this, $response);

        // Prefix controller action
        $action = 'action' . $this->action;

        // Check that action exists
        if ($controller_class->hasMethod($action) === false) {
            throw new RequestException(404);
        }

        $controllerAction = $controller_class->getMethod($action);

        // Check if number of parameters match
        if (count($this->action_args) < $controllerAction->getNumberOfRequiredParameters() || count($this->action_args) > $controllerAction->getNumberOfParameters()) {
            throw new RequestException(404);
        }

        // Run pre-action method
        $controller->before();

        // Run action
        $response->body($controllerAction->invokeArgs($controller, $this->action_args));

        // Run post-action method
        $controller->after();

        // Return response
        return $response;
    }

    /**
     * Returns the name of the requested action.
     *
     * <code>
     *     $action_name = $this->request->action();
     * </code>
     *
     * @access  public
     * @return string
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * Returns the name of the requested controller.
     *
     * <code>
     *     $action_name = $this->request->controller();
     * </code>
     *
     * @access  public
     * @return string
     */
    public function controller()
    {
        return $this->controller;
    }

    /**
     * Is this the main request?
     *
     * <code>
     *     if ($this->request->isMain()) {
     *	      // Do something
     *     }
     * </code>
     *
     * @access  public
     * @return boolean
     */
    public function is_main()
    {
        return $this->is_main;
    }

    /**
     * Returns the ip of the client that made the request.
     *
     * <code>
     *     $ip = $this->request->ip();
     * </code>
     *
     * @access  public
     * @return string
     */
    public static function ip()
    {
        return static::$ip;
    }

    /**
     * From where did the request originate?
     *
     * <code>
     *     $referer = $this->request->referer();
     * </code>
     *
     * @access  public
     * @param   string  (optional) Value to return if no referer is set
     * @return string
     */
    public static function referer($default = '')
    {
        return empty(static::$referer) ? $default : static::$referer;
    }

    /**
     * Returns the route of the main request.
     *
     * <code>
     *     $route = $this->request->route();
     * </code>
     *
     * @access  public
     * @return string
     */
    public static function route()
    {
        return static::$main_route;
    }

    /**
     * Which request method was used?
     *
     * <code>
     *     $method = $this->request->method();
     * </code>
     *
     * @access  public
     * @return string
     */
    public static function method()
    {
        return static::$method;
    }

    /**
     * Is this an Ajax request?
     *
     * <code>
     *     if ($this->request->is_ajax()) {
     *	      // Do something
     *     }
     * </code>
     *
     * @access  public
     * @return boolean
     */
    public static function is_ajax()
    {
        return static::$is_ajax;
    }

    /**
     * Was the reqeust made using HTTPS?
     *
     * <code>
     *     if ($this->request->isSecure()) {
     *	      // Do something
     *     }
     * </code>
     *
     * @access  public
     * @return boolean
     */
    public static function isSecure()
    {
        return static::$secure;
    }
}

class RequestException extends RuntimeException{}
