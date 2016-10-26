<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Route;
use Garden\Exception;
use Garden\Request;
use Garden\Addons;
use Garden\Event;

/**
 * Maps paths to controllers that act as RESTful resources.
 *
 * The following are examples of urls that will map using this resource route.
 * - METHOD /controller/:id -> method
 * - METHOD /controller/:id/action -> methodAction
 * - GET /controller -> index
 * - METHOD /controller/action -> methodAction
 */
class Resource extends \Garden\Route {
    protected $controllerPattern = '%sController';

    /**
     * @var array An array of controller method names that can't be dispatched to by name.
     */
    public static $specialActions = ['delete', 'get', 'index', 'initialize', 'options', 'patch', 'post'];

    /**
     * Initialize an instance of the {@link ResourceRoute} class.
     *
     * @param string $root The url root of that this route will map to.
     * @param string $controllerPattern A pattern suitable for {@link sprintf} that will map
     * a path to a controller object name.
     */
    public function __construct($root = '', $controllerPattern = null) {
        $this->pattern($root);

        if ($controllerPattern !== null) {
            $this->controllerPattern = $controllerPattern;
        }
    }

    /**
     * Dispatch the route.
     *
     * @param Request $request The current request we are dispatching against.
     * @param array &$args The args to pass to the dispatch.
     * These are the arguments returned from {@link Route::matches()}.
     * @return mixed Returns the result from the controller method.
     * @throws Exception\NotFound Throws a 404 when the path doesn't map to a controller action.
     * @throws Exception\MethodNotAllowed Throws a 405 when the http method does not map to a controller action,
     * but other methods do.
     */
    public function dispatch(Request $request, array &$args) {
        $controller = new $args['controller']();
        $method = strtolower($args['method']);
        $actionArgs = $args['args'];
        $action = $args['action'];
        $actions = ['get' => 'index', 'post' => 'post', 'options' => 'options', 'delete' => 'delete'];

        $initialize = method_exists($controller, 'initialize');

        if (!isset($actions[$method])) {
            // The http method isn't allowed.
            $allowed = array_keys($actions);
            throw new Exception\MethodNotAllowed($method, $allowed);
        }
        if (!$action) {
            $action = $this->actionExists($controller, $actions[$method]);
        } else {
            $action = $this->actionExists($controller, $action, $method, false);
        }

        if(!$action) {
            $allowed = $this->allowedMethods($controller, $action);
            if (!empty($allowed)) {
                // At least one method was allowed for this action so throw an exception.
                throw new Exception\MethodNotAllowed($method, $allowed);
            } else {
                // not found
                throw new Exception\Pass;
            }
        }

        // Make sure the number of action arguments match the action method.
        $actionMethod = new \ReflectionMethod($controller, $action);
        $action = $actionMethod->getName(); // make correct case.
        $actionParams = $actionMethod->getParameters();

        if(!$actionMethod->isPublic()) {
            throw new Exception\Pass;
        }

        // Fill in missing default parameters.
        foreach ($actionParams as $i => $param) {
            $paramName = $param->getName();

            if ($this->isMapped($paramName)) {
                // The parameter is mapped to a specific request item.
                array_splice($actionArgs, $i, 0, [$this->mappedData($paramName, $request)]);
            } elseif (!isset($actionArgs[$paramName])) {
                if ($param->isDefaultValueAvailable()) {
                    $actionArgs[$paramName] = $param->getDefaultValue();
                } else {
                    throw new Exception\NotFound("Missing argument $i for {$args['controller']}::$action().");
                }
            } elseif ($this->failsCondition($paramName, $actionArgs[$paramName])) {
                throw new Exception\NotFound("Invalid argument '{$actionArgs[$paramName]}' for {$paramName}.");
            }
        }

        $request->setEnv('ACTION', $action);
        $request->setEnv('CONTROLLER', $args['controller']);

        $response = \Garden\Response::create();

        if ($initialize) {
            $initArgs = array_merge(['action' => $action], $actionArgs);
            Event::callUserFuncArray([$controller, 'initialize'], $initArgs);
        }

        $result = Event::callUserFuncArray([$controller, $action], $actionArgs);
        return $result ?: \Garden\Response::current();
    }

    /**
     * Tests whether or not a string is a valid identifier.
     *
     * @param string $str The string to test.
     * @return bool Returns true if {@link $str} can be used as an identifier.
     */
    protected static function isIdentifier($str) {
        if (preg_match('`[_a-zA-Z][_a-zA-Z0-9]{0,30}`i', $str)) {
            return true;
        }
        return false;
    }

    /**
     * Tests whether a controller action exists.
     *
     * @param object $object The controller object that the method should be on.
     * @param string $action The name of the action.
     * @param string $method The http method.
     * @param bool $special Whether or not to blacklist the special methods.
     * @return string Returns the name of the action method or an empty string if it doesn't exist.
     */
    protected function actionExists($object, $action, $method = '', $special = false) {
        // p($object, $action);
        if ($special && in_array($action, self::$specialActions)) {
            return '';
        }

        // Short circuit on a badly named action.
        if (!$this->isIdentifier($action)) {
            return '';
        }

        if ($method && $method !== $action) {
            $calledAction = $method.'_'.$action;
            if (Event::methodExists($object, $calledAction)) {
                return $calledAction;
            }
        }
        $calledAction = $action;
        if (Event::methodExists($object, $calledAction)) {
            return $calledAction;
        }
        return '';
    }

    /**
     * Find the allowed http methods on a controller object.
     *
     * @param object $object The object to test.
     * @param string $action The action to test.
     * @return array Returns an array of allowed http methods.
     */
    protected function allowedMethods($object, $action) {
        $allMethods = [
            Request::METHOD_GET, Request::METHOD_POST, Request::METHOD_DELETE,
            Request::METHOD_PATCH, Request::METHOD_PUT,
            Request::METHOD_HEAD, Request::METHOD_OPTIONS
        ];

        // Special actions should not be considered.
        if (in_array($action, self::$specialActions)) {
            return [];
        }

        if (Event::methodExists($object, $action)) {
            // The controller has the named action and thus supports all methods.
            return $allMethods;
        }

        // Loop through all the methods and check to see if they exist in the form $method.'_'.$action.
        $allowed = [];
        foreach ($allMethods as $method) {
            if (Event::methodExists($object, $method.'_'.$action)) {
                $allowed[] = $method;
            }
        }
        return $allowed;
    }

    /**
     * Try matching a route to a request.
     *
     * @param Request $request The request to match the route with.
     * @param Application $app The application instantiating the route.
     * @return array|null Whether or not the route matches the request.
     * If the route matches an array of args is returned, otherwise the function returns null.
     */
    public function matches(Request $request, \Garden\Application $app) {
        if (!$this->matchesMethods($request)) {
            return null;
        }

        if ($this->getMatchFullPath()) {
            $path = $request->getFullPath();
        } else {
            $path = $request->getPathExt();
        }

        $regex = $this->getPatternRegex($this->pattern());

        $controller = $action = false;
        $printArgs = array();

        if (preg_match($regex, $path, $matches)) {
            $args = [];
            foreach ($matches as $key => $value) {
                if ($key === 'controller' OR $key === 'action') {
                    $$key = $value;
                    $printArgs[] = ucfirst($value);
                } elseif (!is_numeric($key)) {
                    $args[$key] = $value;
                    $printArgs[] = ucfirst($value);
                }
            }
        } else {
            return null;
        }


        // Check to see if a class exists with the desired controller name.
        // If a controller is found then it is responsible for the route, regardless of any other parameters.
        $basename = vsprintf($this->controllerPattern, $printArgs);
        if (class_exists('\Garden\Addons', false)) {
            list($classname) = Addons::classMap($basename);

            if (!$classname && class_exists($basename)) {
                $classname = $basename;
            }
        } elseif (class_exists($basename)) {
            $classname = $basename;
        } else {
            $classname = '';
        }

        if (!$classname) {
            return null;
        }

        $result = array(
            'controller' => $classname,
            'action'     => $action,
            'method'     => $request->getMethod(),
            'path'       => $path,
            'args'       => $args,
            'query'      => $request->getQuery()
        );
        return $result;
    }

    /**
     * Tests whether an argument fails against a condition.
     *
     * @param string $name The name of the parameter.
     * @param string $value The value of the argument.
     * @return bool|null Returns one of the following:
     * - true: The condition fails.
     * - false: The condition passes.
     * - null: There is no condition.
     */
    protected function failsCondition($name, $value) {
        $name = strtolower($name);
        if (isset($this->conditions[$name])) {
            $regex = $this->conditions[$name];
            return !preg_match("`^$regex$`", $value);
        }

        if (isset(self::$globalConditions[$name])) {
            $regex = self::$globalConditions[$name];
            return !preg_match("`^$regex$`", $value);
        }

        return null;
    }
}
