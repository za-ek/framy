<?php
namespace Zaek\Framy\Routing;

use OutOfBoundsException;

class RoutePrefix extends RouteGroup {
    private $prefix;
    public function __construct($prefix, $routes)
    {
        $this->prefix = $prefix;

        $tmp = [];
        foreach($routes as $route => $target) {
            if(is_object($target) && $target instanceof RouteGroup) {
                $this->count += $target->count();
                foreach($target->routes as $route) {
                    $tmp[] = [$route[0], $route[1], $this->prefix . $route[2]];
                }
            } else {
                $this->count++;
                $tmp[] = [$route, $target, $prefix];
            }
        }

        foreach($tmp as $route) {
            if(strstr($route[0], ' ')) {
                $a = explode(' ', $route[0]);
                $key = $a[0] . ' ' . $route[2] . $a[1];
            } else {
                $key = $route[2] . $route[1];
            }
            $this->routes[] = [$key, $route[1], ''];
        }
    }
}