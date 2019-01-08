<?php
namespace Zaek\Framy\Request;

class Web extends Request
{
    /**
     * @return mixed
     * @throws InvalidRequest
     */
    public function getMethod()
    {
        if($_SERVER['REQUEST_METHOD'] == 'CLI') {
            throw new InvalidRequest('Unsupported method');
        }

        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * @param mixed ...$keys
     * @return array|false
     */
    public function post(...$keys)
    {
        if (count($keys) > 0) {
            if(is_array($keys[0])) {
                return array_combine($keys[0], $_POST);
            } else {
                return array_combine($keys, $_POST);
            }
        }

        return $_POST;
    }

    /**
     * @param mixed ...$keys
     * @return array|false
     */
    public function get(...$keys)
    {
        if (count($keys) > 0) {
            return array_combine($keys, $_GET);
        }

        return $_GET;
    }

    /**
     * @return mixed
     */
    public function files()
    {
        return $_FILES;
    }
}