<?php

namespace App;

use DOMException;
use Exception;

class Route {
    private static array $routes = [];
    private static array $namedRoutes = [];
    private static array $globalMiddlewares = [];
    private static array $groupMiddlewares = [];
    private static string $prefix = '';
    private static ?int $currentRoute = null;

    /**
     * Add a new route to the router.
     *
     * @param string $method HTTP method (GET, POST, etc.).
     * @param string $path Route path.
     * @param callable|array $callback Route callback.
     * @return Route
     */
    private static function addRoute(string $method, string $path, callable|array $callback): Route
    {
        $fullPath = self::$prefix . $path;
        $route = [
            'method' => $method,
            'path' => $fullPath,
            'callback' => $callback,
            'where' => [],
            'middlewares' => array_merge(self::$groupMiddlewares, self::$globalMiddlewares),
            'name' => null
        ];

        self::$routes[] = $route;
        self::$currentRoute = count(self::$routes) - 1;

        return new self();
    }

    public static function get(string $path, callable|array $callback): Route
    {
        return self::addRoute('GET', $path, $callback);
    }

    public static function post(string $path, callable|array $callback): Route
    {
        return self::addRoute('POST', $path, $callback);
    }

    public static function put(string $path, callable|array $callback): Route
    {
        return self::addRoute('PUT', $path, $callback);
    }

    public static function patch(string $path, callable|array $callback): Route
    {
        return self::addRoute('PATCH', $path, $callback);
    }

    public static function delete(string $path, callable|array $callback): Route
    {
        return self::addRoute('DELETE', $path, $callback);
    }

    public static function options(string $path, callable|array $callback): Route
    {
        return self::addRoute('OPTIONS', $path, $callback);
    }

    /**
     * Add multiple routes with the same callback.
     *
     * @param array $methods HTTP methods.
     * @param string $path Route path.
     * @param callable|array $callback Route callback.
     * @return Route
     */
    public static function addMatch(array $methods, string $path, callable|array $callback): Route
    {
        foreach ($methods as $method) {
            self::addRoute($method, $path, $callback);
        }
        return new self();
    }

    public static function any(string $path, callable|array $callback): Route
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        return self::addMatch($methods, $path, $callback);
    }

    /**
     * Generate a URL for a named route.
     *
     * @param string $name Route name.
     * @param array $params Parameters for the route.
     * @return string
     * @throws Exception
     */
    public static function generateUrl(string $name, array $params = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new Exception("Route {$name} not defined.");
        }

        $route = self::$namedRoutes[$name];
        $path = $route['path'];

        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }

        return url($path);
    }

    /**
     * Add middleware to the current or global scope.
     *
     * @param mixed $middleware Middleware to add.
     * @return Route
     * @throws Exception
     */
    public static function middleware(mixed $middleware): Route
    {
        if (!is_callable($middleware) && !is_string($middleware) && !is_array($middleware)) {
            throw new Exception("Invalid middleware type");
        }

        if (self::$currentRoute !== null) {
            self::$routes[self::$currentRoute]['middlewares'][] = $middleware;
        } else {
            self::$globalMiddlewares[] = $middleware;
        }
        return new self();
    }

    /**
     * Group routes with common attributes.
     *
     * @param mixed $attributes Group attributes.
     * @param callable $callback Group callback.
     */
    public static function group(mixed $attributes, callable $callback): void
    {
        $parentPrefix = self::$prefix;
        $parentGroupMiddlewares = self::$groupMiddlewares;

        if (is_array($attributes)) {
            if (isset($attributes['prefix'])) {
                self::$prefix .= $attributes['prefix'];
            }
            if (isset($attributes['middleware'])) {
                $middlewares = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
                self::$groupMiddlewares = array_merge(self::$groupMiddlewares, [$middlewares]);
            }
        } else {
            self::$prefix .= $attributes;
        }

        call_user_func($callback);

        self::$prefix = $parentPrefix;
        self::$groupMiddlewares = $parentGroupMiddlewares;
    }

    /**
     * Set regex conditions for route parameters.
     *
     * @param string $param Parameter name.
     * @param string $regex Regex condition.
     * @return Route
     */
    public static function where(string $param, string $regex): Route
    {
        if (self::$currentRoute !== null) {
            self::$routes[self::$currentRoute]['where'][$param] = "/^$regex$/";
        }
        return new self();
    }

    /**
     * Define a view route.
     *
     * @param string $path Route path.
     * @param string $viewName View name.
     * @param array $data Data to pass to the view.
     * @return Route
     */
    public static function view(string $path, string $viewName, array $data = []): Route
    {
        return self::addRoute('GET', $path, function($params = []) use ($viewName, $data) {
            View::render($viewName, array_merge($data, $params));
        });
    }

    /**
     * Define an API route.
     *
     * @param string $path Route path.
     * @param string $apiName API file name.
     * @param array $data Data to pass to the API.
     * @return Route
     */
    public static function api(string $path, string $apiName, array $data = []): Route
    {
        return self::addRoute('POST', $path, function($params = []) use ($apiName, $data) {
            extract(array_merge($data, $params));
            require resources_path("api" . DIRECTORY_SEPARATOR . "$apiName.php");
        });
    }

    /**
     * Get the current request URI.
     *
     * @return string
     */
    private static function getUri(): string
    {
        return $_GET['route'] ?? '/';
    }

    /**
     * Run the router and dispatch the request.
     *
     * @throws Exception
     */
    public static function dispatch(): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = self::getUri();
        foreach (self::$routes as $route) {
            if ($route['method'] === $method && self::match($path, $route['path'], $params)) {
                if (self::validateParams($params, $route['where'])) {
                    $_SERVER['SentParameters'] = $params;
                    foreach ($route['middlewares'] as $middleware) {
                        if (is_callable($middleware)) {
                            call_user_func($middleware);
                        } elseif (is_array($middleware) && class_exists($middleware[0])) {
                            $middleware_params = array_slice($middleware, 1);
                            if (empty($middleware_params)) {
                                $middleware_params = [];
                            } elseif (is_array($middleware_params[0])) {
                                $middleware_params = $middleware_params[0];
                            }
                            (new $middleware[0])->handle($middleware_params);
                        } elseif (class_exists($middleware)) {
                            (new $middleware)->handle([]);
                        } else {
                            throw new Exception("Invalid middleware configuration");
                        }
                    }
                    return call_user_func_array($route['callback'], [$params]);
                }
            }
        }

        if ($method !== 'GET') {
            showError(405, 405);
        } else {
            showError(404, 404);
        }
    }


    private static function injectJsToHead(string $html): string {
        if (stripos($html, '<html') === false || stripos($html, '</html') === false) {
            return $html;
        }

        if (stripos($html, '<head') === false || (stripos($html, '</head') === false)) {
            return $html;
        }

        $jsCode = '<script type="text/javascript">' . localize_script()->withoutTag()->outputDefault() . localize_script()->withoutTag()->output() . '</script>';
        $jsCode = Minify::html($jsCode);

        $updatedHtml = preg_replace('/<\/head\s*>/i', $jsCode . "</head>", $html, 1);
        return $updatedHtml ?: $html;
    }

    /**
     * @throws DOMException
     * @throws Exception
     */
    public static function run(): void
    {
        ob_start();
        self::dispatch();
        $output = ob_get_contents();
        ob_end_clean();

        if (config('app.minify')['html'])
        {
            echo self::injectJsToHead($output);
        }
        else
        {
            echo $output;
        }
    }

    public static function include_routes(): Route
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            require_once app_path('routes\\api.php');
        } else {
            require_once app_path('routes\\web.php');
        }
        return new self();
    }


    /**
     * Redirect from one URL to another.
     *
     * @param string $old_url The original URL.
     * @param string $new_url The new URL.
     * @return Route
     */
    public static function redirect(string $old_url, string $new_url): Route
    {
        return self::addRoute('GET', $old_url, function () use ($new_url){
            if (!filter_var($new_url, FILTER_VALIDATE_URL)) {
                redirect(url($new_url));
            } else {
                redirect($new_url);
            }
        });
    }

    /**
     * Match the current request path to a route.
     *
     * @param string $requestPath The current request path.
     * @param string $routePath The route path.
     * @param array|null $params The route parameters.
     * @return bool
     */
    private static function match(string $requestPath, string $routePath, array|null &$params): bool
    {
        $requestParts = explode('/', trim($requestPath, '/'));
        $routeParts = explode('/', trim($routePath, '/'));

        if (count($requestParts) !== count($routeParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $key => $part) {
            if (preg_match('/^{\w+}$/', $part)) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $requestParts[$key];
            } elseif ($requestParts[$key] !== $part) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate route parameters against their conditions.
     *
     * @param array $params The route parameters.
     * @param array $conditions The parameter conditions.
     * @return bool
     */
    private static function validateParams(array $params, array $conditions): bool
    {
        foreach ($conditions as $key => $regex) {
            if (!isset($params[$key]) || !preg_match($regex, $params[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Name the current route.
     *
     * @param string $name The name of the route.
     * @return Route
     * @throws Exception
     */
    public static function name(string $name): Route
    {
        if (self::$currentRoute !== null) {
            if (!isset(self::$namedRoutes[$name])) {
                $route = self::$routes[self::$currentRoute];
                $route['name'] = $name;
                self::$routes[self::$currentRoute] = $route;
                self::$namedRoutes[$name] = $route;
            } else {
                throw new Exception('The Route name "' . $name . '" already exists. Choose another name');
            }
        }
        return new self();
    }
}