<?php
namespace Zaek\Framy\Request;

class Web extends Request
{
    public function getMethod()
    {
        if($_SERVER['REQUEST_METHOD'] == 'CLI') {
            throw new InvalidRequest('Unsupported method');
        }

        return $_SERVER['REQUEST_METHOD'];
    }

    public function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }
}