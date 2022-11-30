<?php
namespace Zaek\Framy\Routing;

use OutOfBoundsException;

class RoutePrefix implements \SeekableIterator, \Countable {
    private string $prefix;
    private array $routes = [];

    private $count = 0;
    private $position = 0;

    public function __construct($prefix, $routes) {
        $this->prefix = $prefix;

        $tmp = [];
        foreach($routes as $route => $target) {
            if(is_object($target) && $target instanceof RoutePrefix) {
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

    public function current(): mixed
    {
        return $this->routes[$this->position][1];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): mixed
    {
        return $this->routes[$this->position][0];
    }

    public function valid() : bool
    {
        $pos = $this->position;
        return isset($this->routes[$this->position]) || count(array_filter($this->routes, function($val) use($pos) {
                return $val[0] === $pos;
        })) > 0;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function seek(int $offset): void
    {
        if(!isset($this->routes[$offset])) {
            throw new OutOfBoundsException("invalid seek position({$offset})");
        }
        $this->position = $offset;
    }

    public function count(): int
    {
        return $this->count;
    }
}