<?php
declare(strict_types = 1);

namespace App;

class Router
{
    protected array $routes = [];

    public function addRoute(string $method, string $url, callable $target): void
    {
        $this->routes[$method][$url] = $target;
    }

    /**
     * Looking for a url match in registered routers
     * @return void
     */
    public function matchRoute(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_SERVER['REQUEST_URI'];
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routeUrl => $target) {
                $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $routeUrl);
                if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    call_user_func_array($target, $params);
                    return;
                }
            }
        }

        if ($this->runDefaultRoute($url)) {
            return;
        }

        $this->call404Page();
    }

    /**
     * Default router, called before the 404 page, searching for a file in the static folder
     * @param $url
     * @return bool
     */
    private function runDefaultRoute($url): bool
    {
        $urlData = parse_url($url);
        $filePath = $urlData['path'] ?? '';

        if ($filePath === '' || $filePath === '/') {
            $filePath = 'index.html';
        }

        preg_match('#(\.[a-zA-Z\d]+)$#', $filePath, $matches);
        if (!isset($matches[1])) {
            $filePath .= '.html';
        }

        if (file_exists(getFilePath($filePath, 'static'))) {
            include getFilePath($filePath, 'static');
            return true;
        }
        return false;
    }

    private function call404Page(): void
    {
        header('HTTP/1.0 404 Not Found');
        include getFilePath('404.html');
    }
}