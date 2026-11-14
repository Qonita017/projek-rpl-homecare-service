<?php
require_once 'Middleware.php';

class Router {
    private $routes = [];

    public function get($url, $target, $middlewares = []) {
        $this->routes["GET"][$url] = ['target' => $target, 'middlewares' => $middlewares];
    }

    public function post($url, $target, $middlewares = []) {
        $this->routes["POST"][$url] = ['target' => $target, 'middlewares' => $middlewares];
    }

    public function dispatch($url) {
        $method = $_SERVER["REQUEST_METHOD"];
        $url = rtrim($url, "/");
        if (empty($url)) $url = "/";

        if (isset($this->routes[$method][$url])) {
            $route = $this->routes[$method][$url];
            $target = $route['target'];
            $middlewares = $route['middlewares'];

            // Run Middlewares
            foreach ($middlewares as $middleware) {
                Middleware::handle($middleware);
            }

            list($controller, $action) = explode("@", $target);
            
            require_once "../app/Controllers/" . $controller . ".php";
            $controllerInstance = new $controller();
            $controllerInstance->$action();
        } else {
            echo "<h1>404 Not Found</h1><p>The page you requested was not found.</p>";
        }
    }
}
