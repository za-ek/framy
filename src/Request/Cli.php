<?php
namespace Zaek\Framy\Request;

class Cli extends Request
{
    public function getMethod()
    {
        return 'CLI';
    }

    public function getUri()
    {
        return (!empty($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '');
    }

    public function post(...$keys)
    {
        return [];
    }

    public function get(...$keys)
    {
        return [];
    }

    public function files()
    {
        return [];
    }
}