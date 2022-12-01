<?php
namespace Zaek\Framy\Routing;

use JetBrains\PhpStorm\ArrayShape;
use SeekableIterator;
use Zaek\Framy\Action;
use Zaek\Framy\Request\Request;
use Zaek\Framy\Response\Json;
use Zaek\Framy\Response\Web;

/**
 * Class Router
 * @package Zaek\Routing
 */
class Router
{
    protected static string $default_output = 'plain';

    /**
     * @var Route[]
     */
    public array $routes = [];

    /**
     * Router constructor.
     * @param array $array
     * @throws InvalidRoute
     */
    public function __construct(SeekableIterator|array $array = [])
    {
        foreach ($array as $route => $target) {
            $this->addRoute($route, $target);
        }
    }

    public static function getResponseClass ($code) : string
    {
        return [
            'plain' => Web::class,
            'html' => Web::class,
            'json' => Json::class,
        ][$code] ?? Web::class;
    }

    /**
     * @param string $route "GET|HEAD|... /path/<arg>"
     * @param mixed $target
     * @throws InvalidRoute
     */
    public function addRoute(string $route, mixed $target) : static
    {
        if(is_array($target) && array_key_exists('target', $target)) {
            $routeTarget = $target['target'];
        } else {
            $routeTarget = $target;
        }

        $route = trim($route);

        if(str_contains($route, '<')) {
            $this->addDynamicRoute($route, $routeTarget, []);
        } else {
            $matches = $this->parseRoute($route);

            if (empty($matches['method']) && empty($matches['path'])) {
                throw new InvalidRoute($route);
            }

            if(in_array('REST', $matches['method'])) {
                $this->addRestRoutes($matches, $routeTarget);
            } else {
                foreach($matches['method'] as $method) {
                    $this->addStaticRoute(
                        $method,
                        $matches['path'],
                        $routeTarget,
                        [
                            'response' => $matches['meta']['response'][$method] ?? self::$default_output
                        ]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @throws InvalidRoute
     */
    public function addRoutes($routes): static
    {
        foreach ($routes as $k => $route) {
            $this->addRoute($k, $route);
        }

        return $this;
    }

    private function addRestRoutes($matches, $target): static
    {
        $path = rtrim($matches['path'], '/');
        $target = rtrim($target, '/');

        foreach([
            ['GET', '', 'List'],
            ['POST', '', 'Add'],
        ] as $item) {
            if(str_contains($matches['path'], '<')) {
                $this->addDynamicRoute($path . $item[1], $target . '/' . $item[2] . '.php');
            } else {
                $this->addStaticRoute(
                    $item[0],
                    $path . $item[1],
                    $target . '/' . $item[2] . '.php',
                    [
                        'response' => 'json'
                    ]
                );
            }
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
                    'response' => 'json'
                ]
            );
        }

        return $this;
    }

    private function addStaticRoute($method, $path, $target, $meta = []) : void
    {
        $this->routes[] = new StaticRoute([
            'method' => $method,
            'path'   => $path,
            'target' => $target,
            'meta'   => $meta
        ]);
    }

    #[ArrayShape(['method' => "array|string[]", 'path' => "string", 'meta' => "array"])]
    private function parseRoute($route) : array
    {
        $meta = [];

        if(!strpos($route, ' ')) {
            $method = Methods::list();
        } else {
            $method = explode('|', substr($route, 0, strpos($route, ' ')));
            foreach($method as $k => $v) {
                $tmp = explode(':', $v);
                $method[$k] = $tmp[0];

                if(!empty($tmp[1])) {
                    $meta['response'][$tmp[0]] = $tmp[1];
                }
            }
        }

        $path = substr($route, strpos($route, ' ') + 1);

        return [
            'method' => $method,
            'path' => $path,
            'meta' => $meta
        ];
    }

    /**
     * @param string $route
     * @param $target
     * @throws InvalidRoute
     */
    private function addDynamicRoute(string $routeOrigin, $target, $meta = []) : void
    {
        $route = $routeOrigin;
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

        $method = strpos($route, ' ') ? substr($route, 0, strpos($route, ' ')) : 'WEB';
        $route = strpos($route, ' ') ? substr($route, strpos($route, ' ') + 1) : $route;
        foreach(explode("|", $method) as $m) {
            $path = '#^' . $route . '$#';

            $tmp = explode(':', $m);
            if (count($tmp) > 1) {
                $meta['response'] = $tmp[1];
                $m = $tmp[0];
            }

            if($m === 'REST') {
                $route = explode(' ', $routeOrigin);
                $route = $route[count($route) - 1];
                $this->addRestRoutes([
                    'method' => [$m],
                    'path' => $route,
                    'meta' => $meta
                ], $target);
            } else {
                $this->routes[] = new DynamicRoute([
                    'method' => $m,
                    'path' => $path,
                    'target' => $target,
                    'vars' => $vars,
                    'meta' => $meta,
                ]);
            }
        }
    }

    /**
     * @param Request $request
     * @return Action\Action
     * @throws NoRoute
     */
    public function getRequestAction(Request $request) : Action\Action
    {
        usort($this->routes, function ($a, $b) {
            if($a->sort === $b->sort) return strlen($a) <=> strlen($b);
            return $a->sort <=> $b->sort;
        });
        foreach($this->routes as $route) {
            if($route->matches($request)) {
                return $route->getAction($request);
            }
        }

        throw new NoRoute;
    }

    public function __printRoutes(): void
    {
        foreach($this->routes as $route) {
            echo $route . "\n";
        }
    }
}