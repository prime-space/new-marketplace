<?php namespace App\Api;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class ApiRouteLoader extends Loader
{
    const LOADER_TYPE = 'api';

    private $container;
    private $configPath;

    private $routes;

    public function __construct(ContainerInterface $container, string $configPath)
    {
        $this->container = $container;
        $this->configPath = $configPath;
        $this->routes = new RouteCollection();
    }

    /**
     * @inheritdoc
     */
    public function load($resource, string $type = null)
    {
        $config = $this->getConfig($this->configPath.$resource);
        $defaults = [
            '_controller' => $config['controller'],
        ];
        $requirements = [];
        $options = [];
        $host = $config['host'] ?? null;
        $schemes = [];

        foreach ($config['routes'] as $routeName => $routeDefinition) {
            $prefix = $config['prefix'] ?? null;
            $path = $prefix !== null ? "/{$config['prefix']}{$routeDefinition['path']}" : $routeDefinition['path'];
            $defaults['handler'] = $this->parseHandler($routeDefinition['handler']);
            $methods = $routeDefinition['methods'];
            $route = new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods);
            $this->routes->add($routeName, $route);
        }

        return $this->routes;
    }

    /**
     * @inheritdoc
     */
    public function supports($resource, string $type = null)
    {
        return self::LOADER_TYPE === $type;
    }

    private function getConfig(string $path)
    {
        $config = Yaml::parseFile($path);
        $this->validateConfig($config);

        return $config;
    }

    private function validateConfig(array $config)
    {
        //@TODO
    }

    private function parseHandler(string $handlerDefinition)
    {
        if (!preg_match('/^([a-z\\\]+)::([a-z]+)$/i', $handlerDefinition, $matches)) {
            throw new \UnexpectedValueException($handlerDefinition);
        }
        $serviceName = $matches[1];
        $methodName = $matches[2];

        $service = $this->container->get($serviceName);
        $handler = [$service, $methodName];
        if (!is_callable($handler)) {
            throw new \RuntimeException("$handlerDefinition is not callable");
        }

        return [$serviceName, $methodName];
    }
}
