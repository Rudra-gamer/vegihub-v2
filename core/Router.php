<?php

class Router {
    private static $routes = [];
    private static $currentPrefix = '';

    public static function get($uri, $action) {
        self::addRoute('GET', $uri, $action);
    }

    public static function post($uri, $action) {
        self::addRoute('POST', $uri, $action);
    }

    public static function group($prefix, $callback) {
        $previousPrefix = self::$currentPrefix;
        self::$currentPrefix .= '/' . trim($prefix, '/');
        $callback();
        self::$currentPrefix = $previousPrefix;
    }

    private static function addRoute($method, $uri, $action) {
        $uri = self::$currentPrefix . '/' . trim($uri, '/');
        $uri = '/' . trim($uri, '/');
        if ($uri === '/') $uri = '/';

        self::$routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
        ];
    }

    public static function dispatch($requestUri, $requestMethod) {
        $basePath = parse_url(APP_URL, PHP_URL_PATH) ?? '';
        $basePath = rtrim(urldecode($basePath), '/');
        $requestUri = strtok($requestUri, '?');
        $requestUri = urldecode($requestUri);
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        if (strpos($requestUri, '/public') === 0) {
            $requestUri = substr($requestUri, 7);
        }
        
        $requestUri = '/' . trim($requestUri, '/');
        if ($requestUri === '/') $requestUri = '/';

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) continue;

            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestUri, $matches)) {
                $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
                return self::callAction($route['action'], $params);
            }
        }
        http_response_code(404);
        include VIEW_PATH . '/errors/404.php';
        exit;
    }

    private static function callAction($action, $params) {
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }

        if (is_string($action) && strpos($action, '@') !== false) {
            list($controller, $method) = explode('@', $action);
            $controllerFile = BASE_PATH . '/controllers/' . $controller . '.php';
            
            if (!file_exists($controllerFile)) {
                throw new Exception("Controller file not found: {$controller}");
            }
            
            require_once $controllerFile;
            
            if (!class_exists($controller)) {
                throw new Exception("Controller class not found: {$controller}");
            }
            
            $instance = new $controller();
            
            if (!method_exists($instance, $method)) {
                throw new Exception("Method not found: {$controller}@{$method}");
            }
            
            return call_user_func_array([$instance, $method], $params);
        }

        throw new Exception("Invalid route action");
    }

    public static function getRoutes() {
        return self::$routes;
    }
}
