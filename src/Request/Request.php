<?php
namespace Zaek\Framy\Request;

abstract class Request
{
    /**
     * @var string
     */
    protected $_method  = '';
    /**
     * @var string
     */
    protected $_uri     = '';
    /**
     * @var array
     */
    protected $_post    = [];
    /**
     * @var array
     */
    protected $_get     = [];
    /**
     * @var array
     */
    protected $_files   = [];

    /**
     * @return mixed
     * @throws InvalidRequest
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * @param mixed ...$keys
     * @return array|false
     */
    public function post(...$keys)
    {
        if (count($keys) > 0) {
            return array_combine($keys, $this->_post);
        }

        return $this->_post;
    }

    /**
     * @param mixed ...$keys
     * @return array|false
     */
    public function get(...$keys)
    {
        if (count($keys) > 0) {
            return array_intersect_key($this->_get, array_flip($keys));
        }

        return $this->_get;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addGet($key, $value)
    {
        $this->_get[$key] = $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addPost($key, $value)
    {
        $this->_post[$key] = $value;
    }

    /**
     * @return array
     */
    public function files()
    {
        return $this->_files;
    }
}