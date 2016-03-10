<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2014 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Route;
use Garden\Request;
use Garden\Application;

/**
 * A route that maps urls to callbacks.
 */
class Callback extends \Garden\Route {
    /// Properties ///

    /**
     *
     * @var callable The callback to call on a matching pattern.
     */
    protected $callback;

    /// Methods ///

    /**
     * Initialize an instance of the {@link CallbackRoute} class.
     *
     * @param string $pattern The pattern to match to.
     * @param callable $callback The callback to call when the url matches.
     */
    public function __construct($pattern, callable $callback) {
        $this->pattern($pattern);
        $this->setCallback($callback);
    }

    /**
     * Dispatch the matched route and call its callback.
     *
     * @param Request $request The request to dispatch.
     * @param array &$args The arguments returned from {@link CallbackRoute::dispatch()}.
     * @return mixed Returns the result of the callback.
     */
    public function dispatch(Request $request, array &$args) {
        $callback = $args['callback'];
        $callback_args = reflect_args($callback, $args['args']);

        $result = call_user_func_array($callback, $callback_args);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(Request $request, Application $app) {
        if (!$this->matchesMethods($request)) {
            return null;
        }

        if ($this->getMatchFullPath()) {
            $path = $request->getFullPath();
        } else {
            $path = $request->getPath();
        }
        $regex = $this->getPatternRegex($this->pattern());

        if (preg_match($regex, $path, $matches)) {
            // This route matches so extract the args.
            $args = array();
            foreach ($matches as $key => $value) {
                if (!is_numeric($key)) {
                    $args[$key] = $value;
                }
            }
            $result = array(
                'callback' => $this->callback,
                'args' => $args,
                );
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Get the callback for the route.
     *
     * @return callable Returns the current callback.
     */
    public function getCallback() {
        return $this->callback;
    }

    /**
     * Set the callback for the route.
     *
     * @param callable $callback The new callback to set.
     * @return CallbackRoute Retuns $this for fluent calls.
     */
    public function setCallback(callable $callback) {
        $this->callback = $callback;
        return $this;
    }
}

