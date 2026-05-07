<?php

class Router {
    private array $routes = [];

    public function add(string $method, string $pattern, string $controller, string $action): void {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => $pattern,
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    public function get(string $pattern, string $controller, string $action): void {
        $this->add('GET', $pattern, $controller, $action);
    }

    public function post(string $pattern, string $controller, string $action): void {
        $this->add('POST', $pattern, $controller, $action);
    }

    public function any(string $pattern, string $controller, string $action): void {
        $this->add('GET', $pattern, $controller, $action);
        $this->add('POST', $pattern, $controller, $action);
    }

    public function dispatch(string $url, string $method): void {
        $url = trim($url, '/');
        if ($url === '') $url = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) continue;

            $regex = $this->patternToRegex($route['pattern']);
            if (preg_match($regex, $url, $matches)) {
                array_shift($matches);
                $this->callAction($route['controller'], $route['action'], $matches);
                return;
            }
        }

        $this->notFound($url);
    }

    private function patternToRegex(string $pattern): string {
        $pattern = trim($pattern, '/');
        if ($pattern === '') $pattern = '/';
        $regex = preg_replace('#\{([a-zA-Z_]+)\}#', '([^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }

    private function callAction(string $controllerName, string $action, array $params): void {
        $file = __DIR__ . '/../Controller/' . $controllerName . '.php';
        if (!file_exists($file)) {
            throw new RuntimeException("Controller file not found: $file");
        }
        require_once $file;

        if (!class_exists($controllerName)) {
            throw new RuntimeException("Controller class not found: $controllerName");
        }

        $controller = new $controllerName();
        if (!method_exists($controller, $action)) {
            throw new RuntimeException("Action $action not found in $controllerName");
        }

        call_user_func_array([$controller, $action], $params);
    }

    private function notFound(string $url): void {
        http_response_code(404);
        echo "<h1>404 - Page introuvable</h1>";
        echo "<p>L'URL <code>" . htmlspecialchars($url) . "</code> ne correspond à aucune route.</p>";
        echo '<p><a href="/TinyTrack/">Retour à l\'accueil</a></p>';
    }
}
