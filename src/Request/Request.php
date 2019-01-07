<?php
namespace Zaek\Request;

abstract class Request
{
    abstract public function getMethod();
    abstract public function getUri();
}