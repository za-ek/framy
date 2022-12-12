<?php
namespace Zaek\Framy\Routing;

abstract class RouteGroup implements \SeekableIterator, \Countable, \ArrayAccess {
    protected array $routes = [];

    protected $count = 0;
    private $position = 0;

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
    public function offsetExists(mixed $offset): bool
    {
        return in_array($offset, array_column($this->routes, 0));
    }
    public function offsetGet(mixed $offset): mixed
    {
        $index = array_search($offset, array_column($this->routes, 0));
        return $this->routes[$index][1];
    }
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if($this->offsetExists($offset)) {
            $index = array_search($offset, array_column($this->routes, 0));
            $this->routes[$index][1] = $value;
        } else {
            $this->routes[] = [$offset, $value, ''];
        }
    }
    public function offsetUnset(mixed $offset): void
    {
        $index = array_search($offset, array_column($this->routes, 0));
        unset($this->routes);
    }
}