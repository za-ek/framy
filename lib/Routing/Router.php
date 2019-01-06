<?php
namespace Zaek\Routing;

use Zaek\Request;

/**
 * Class Router
 * @package Zaek\Routing
 */
class Router
{
    /**
     * List of available HTTP methods
     * @var array
     */
    protected static $available_methods = [
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'DELETE',
        'CONNECT',
        'OPTIONS',
        'TRACE',
    ];

    private $static_routes = [];
    private $dynamic_routes = [];

    /**
     * Router constructor.
     * @param array $array
     * @throws InvalidRoute
     */
    public function __construct($array = [])
    {
        foreach ($array as $route => $match) {
            $this->addRoute($route, $match);
        }
    }

    /**
     * @param string $route "GET|HEAD|... /path/<arg>"
     * @param mixed $target
     * @throws InvalidRoute
     */
    public function addRoute(string $route, $target) : void
    {
        $route = trim($route);

        if(strstr($route, '<')) {
            $this->dynamic_routes[] = $this->prepareDynamicRoute($route, $target);
        } else {
            $matches = $this->parseRoute($route);

            if (empty($matches['method']) && empty($matches['path'])) {
                throw new InvalidRoute($route);
            }

            $this->static_routes[] = [
                'method' => $matches['method'],
                'path' => $matches['path'],
                'target' => $target
            ];
        }
    }

    private function parseRoute($route, $regexp = '(?<path>[^s]*)')
    {
        $regexp = '/' .
            '(?<method>' . implode('|', self::$available_methods) . '){1}' .
            '\s' .
            $regexp .
            '$' .
            '/';

        preg_match_all($regexp, $route, $matches);

        return $matches;
    }

    /**
     * @param string $route
     * @param $target
     * @return array
     * @throws InvalidRoute
     */
    private function prepareDynamicRoute($route, $target)
    {
        $length = strlen($route);
        $inside = false;
        for($i = $length - 1; $i > 0; $i--) {
            if($route[$i] === '>') $inside = true;
            if($route[$i] === '<') $inside = false;

            if(!$inside) {
                if($route[$i] === '/' && $route[$i-1] !== '\\') {
                    $route = substr_replace($route, '\\', $i, 0);
                    $i--;
                }
            }
        }

        $positions = [];

        $lastPos = 0;
        while (($lastPos = strpos($route, '<', $lastPos))!== false) {
            $positions[$lastPos] = '<';
            $lastPos = $lastPos + 1;
        }

        $lastPos = 0;
        while (($lastPos = strpos($route, '>', $lastPos))!== false) {
            $positions[$lastPos] = '>';
            $lastPos = $lastPos + 1;
        }

        ksort($positions);

        // Проверка последовательности <...>
        $index = [];
        $lastVal = '';
        foreach($positions as $k => $val) {
            if($val === $lastVal) {
                throw new InvalidRoute($route);
            }
            $lastVal = $val;
            $index[] = $k;
        }

        $positions = array_chunk($index, 2);
        $vars = [];

        foreach(array_reverse($positions) as $item) {
            $group = substr($route, $item[0], $item[1] - $item[0] + 1);
            if(preg_match_all('/<(?<var>[\w]+):(?<rule>.*)>/', $group, $matches)) {
                $route =
                    substr($route, 0, $item[0]) .
                    "(?<{$matches['var'][0]}>{$matches['rule'][0]})" .
                    substr($route, $item[1] + 1);

                $vars[] = $matches['var'][0];
            }
        }

        $method = strpos($route, ' ') ? substr($route, 0, strpos($route, ' ')) : $route;

        return [
            'method' => strpos($route, ' ') ? substr($route, 0, strpos($route, ' ')) : $route,
            'path' => '#' . substr($route, strlen($method) + 1) . '#',
            'target' => $target,
            'vars' => $vars
        ];
    }

    /**
     * @param $method
     * @param $uri
     * @return mixed
     */
    public function getRequestAction($method, $uri)
    {
        foreach($this->static_routes as $route) {
            if($route['method'][0] === $method && $route['path'][0] === $uri) {
                return $route['target'];
            }
        }

        foreach($this->dynamic_routes as $route) {
            if($route['method'] === $method) {
                if(preg_match_all($route['path'], $uri, $matches)) {
                    foreach($route['vars'] as $var) {
                        $route['target'] = str_replace(
                            '$' . $var,
                            $matches[$var][0],
                            $route['target']
                        );
                    }

                    return $route;
                }
            }
        }
    }
}