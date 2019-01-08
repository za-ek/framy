<?php
namespace Zaek\Framy\Request;

abstract class Request
{
    /**
     * @return mixed
     * @throws InvalidRequest
     */
    abstract public function getMethod();
    abstract public function getUri();
    abstract public function post(...$keys);
    abstract public function get(...$keys);
    abstract public function files();
}