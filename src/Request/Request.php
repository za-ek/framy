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
}