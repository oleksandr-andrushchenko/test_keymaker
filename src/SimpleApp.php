<?php

namespace App;

use PDO;

class SimpleApp
{
    private $db;
    private $providerClass;
    private $provider;
    private $routes = [];

    public function __construct(string $providerClass)
    {
        $this->providerClass = $providerClass;
    }

    public function getProviderClass(): string
    {
        return $this->providerClass;
    }

    public function getDb(): PDO
    {
        if (null === $this->db) {
            $this->db = new PDO(
                'mysql:host=mariadb;dbname=' . getenv('MYSQL_DATABASE') . ';charset=utf8mb4',
                getenv('MYSQL_USER'),
                getenv('MYSQL_PASSWORD')
            );
        }

        return $this->db;
    }

    public function getProvider(): Provider
    {
        if (null === $this->provider) {
            $this->provider = new $this->providerClass($this);
        }

        return $this->provider;
    }

    public function addRoute(callable $matcher, callable $dispatcher): self
    {
        $this->routes[] = [$matcher, $dispatcher];

        return $this;
    }

    public function terminate(int $code, string $header)
    {
        header("HTTP/1.0 $code $header");
        echo $header;
        die;
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = '/' . trim(explode('?', $_SERVER['REQUEST_URI'])[0], '/');
        $params = array_merge($_GET, $_POST);

        foreach ($this->routes as [$matcher, $dispatcher]) {
            if ($args = $matcher($method, $path, $params)) {
                $dispatcher(...(array)$args);
                break;
            }
        }
    }
}