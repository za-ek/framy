<?php
namespace Zaek;

class Request
{
    public function __construct()
    {
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }
}