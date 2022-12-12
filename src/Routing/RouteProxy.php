<?php
namespace Zaek\Framy\Routing;

class RouteProxy extends RouteGroup {
    private $fn;
    public function __construct($fn, $routes)
    {
        $this->fn = $fn;
        foreach($routes as $route => $target) {
            $this->routes[] = [$route, $target, ''];
        }
    }

    public function current(): mixed
    {
        $result = parent::current();
        return call_user_func($this->fn, $result);
    }
}