<?php

final class Router
{
    /** @param array<string, array{controller?: ?string, method?: ?string, middleware?: string[]}> $routes */
    public function __construct(private array $routes)
    {
    }

    public function dispatch(string $path, string $method = 'GET'): void
    {
        $route = trim((string)(parse_url($path, PHP_URL_PATH) ?? ''), '/');
        if ($route === '') {
            $route = '';
        }

        $definition = $this->routes[$route] ?? null;
        if (!is_array($definition) || empty($definition['controller']) || empty($definition['method'])) {
            http_response_code(404);
            require ROOT_PATH . '/resources/views/errors/404.php';
            return;
        }

        foreach ($definition['middleware'] ?? [] as $middleware) {
            $this->runMiddleware($middleware);
        }

        $controller = (string)$definition['controller'];
        $action = (string)$definition['method'];
        require_once controllerFile($controller);
        $handler = new $controller();

        if (!is_callable([$handler, $action])) {
            throw new LogicException(sprintf('Route %s pointe vers une action inexistante.', $route));
        }

        $handler->{$action}();
    }

    private function runMiddleware(string $middleware): void
    {
        [$name, $argument] = array_pad(explode(':', $middleware, 2), 2, null);
        $file = match ($name) {
            'auth' => '/app/Middleware/AuthMiddleware.php',
            'guest' => '/app/Middleware/GuestMiddleware.php',
            'admin' => '/app/Middleware/AdminMiddleware.php',
            'role' => '/app/Middleware/RoleMiddleware.php',
            default => throw new InvalidArgumentException("Middleware inconnu : {$name}."),
        };
        require_once ROOT_PATH . $file;

        $class = match ($name) {
            'auth' => 'AuthMiddleware',
            'guest' => 'GuestMiddleware',
            'admin' => 'AdminMiddleware',
            'role' => 'RoleMiddleware',
        };
        if ($name === 'role') {
            $class::handle((string)$argument);
            return;
        }
        if ($name === 'auth') {
            $class::requireUser();
            return;
        }
        $class::handle();
    }
}
