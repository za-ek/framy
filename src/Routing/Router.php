<?php
namespace Zaek\Framy\Routing;

use Zaek\Framy\Action;
use Zaek\Framy\Action\File;
use Zaek\Framy\Response\Json;

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
        'CLI',
    ];

    /**
     * @var array
     */
    public $static_routes = [];
    /**
     * @var array
     */
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
            $this->addDynamicRoute($route, $target);
        } else {
            $matches = $this->parseRoute($route);

            if (empty($matches['method']) && empty($matches['path'])) {
                throw new InvalidRoute($route);
            }

            if(in_array('REST', $matches['method'])) {
                $this->addRestRoutes($matches, $target);
            } else {
                $this->addStaticRoute($matches['method'], $matches['path'], $target);
            }
        }
    }

    private function addRestRoutes($matches, $target)
    {
        $path = rtrim($matches['path'], '/');
        $target = rtrim($target, '/');

        foreach([
            ['GET', '', 'List'],
            ['POST', '', 'Add'],
        ] as $item) {
            $this->addStaticRoute(
                [$item[0]],
                $path . $item[1],
                $target . '/' . $item[2] . '.php',
                [
                    'response' => Json::class
                ]
            );
        }

        foreach([
            ['GET', '/<id:[\d]+>', 'Item'],
            ['PATCH', '/<id:[\d]+>', 'Update'],
            ['DELETE', '/<id:[\d]+>', 'Delete'],
        ] as $item) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->addDynamicRoute(
                $item[0] . ' ' . $path . $item[1],
                $target . '/' . $item[2] . '.php',
                [
                    'response' => Json::class
                ]
            );
        }
    }

    private function addStaticRoute($methods, $path, $target, $meta = [])
    {
        foreach($methods as $method) {
            $this->static_routes[] = [
                'method' => $method,
                'path'   => $path,
                'target' => $target,
                'meta'   => $meta
            ];
        }
    }

    private function parseRoute($route)
    {
        if(!strpos($route, ' ')) {
            $method = self::$available_methods;
        } else {
            $method = explode('|', substr($route, 0, strpos($route, ' ')));
        }

        $path = substr($route, strpos($route, ' ') + 1);

        return [
            'method' => $method,
            'path' => $path
        ];
    }

    /**
     * @param string $route
     * @param $target
     * @return array
     * @throws InvalidRoute
     */
    private function addDynamicRoute($route, $target, $meta = []) : void
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

        $this->dynamic_routes[] = [
            'method' => strpos($route, ' ') ? substr($route, 0, strpos($route, ' ')) : $route,
            'path'   => '#' . substr($route, strlen($method) + 1) . '#',
            'target' => $target,
            'vars'   => $vars,
            'meta'   => $meta
        ];
    }

    /**
     * @param $method
     * @param $uri
     * @return Action
     * @throws NoRoute
     */
    public function getRequestAction($method, $uri) : Action\Action
    {
        foreach($this->static_routes as $route) {
            if($route['method'] === $method && $route['path'] === $uri) {
                return $this->convertRouteToAction($route);
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

                    return $this->convertRouteToAction($route);
                }
            }
        }

        throw new NoRoute;
    }

    private function convertRouteToAction($route)
    {
        if (is_array($route)) {
            if(array_key_exists('target', $route)) {
                if(is_callable($route['target'])) {
                    $action = new Action\CbFunction($route['target']);
                } else {
                    $action = new File($route['target']);
                }
            } else {
                if (count($route) == 1) {
                    $route = $route[0];
                }
                $action = new Action\CbFunction($route);
            }
        } else if (is_object($route) && $route instanceof Action) {
            $action = $route;
        } else if (is_callable($route)) {
            $action = new Action\CbFunction($route);
        }

        if(!empty($route['meta'])) {
            if(!empty($route['meta']['response'])) {
                $action->setResponse(new $route['meta']['response']);
            }
        }

        return $action;
    }

    public function __printRoutes()
    {
        foreach($this->static_routes as $route) {
            echo "{$route['method']}\t{$route['path']}\n";
        }
    }
}