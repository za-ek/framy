<?php
namespace Zaek\Framy\Request;

abstract class Request implements RequestInterface, RequestBodyInterface
{
    /**
     * @var string
     */
    protected $_method  = '';
    /**
     * @var string
     */
    protected $_uri     = '';

    protected $_body = [];
    protected $_queries = [];

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param mixed ...$keys
     * @return array|mixed
     */
    public function getQueries (...$keys)
    {
        if (count($keys) > 0) {
            return array_intersect_key($this->_queries, array_flip($keys));
        }

        return $this->_queries;
    }

    public function getQuery($key)
    {
        if(array_key_exists($key, $this->_queries)) {
            return $this->_queries[$key];
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addQuery($key, $value)
    {
        $this->_queries[$key] = $value;
    }

    /**
     * @param $type
     * @param $value
     */
    public function addBody($type, $value)
    {
        $this->_body[$type] = $value;
    }

    /**
     * @param mixed ...$keys
     * @return array|mixed
     */
    public function post(...$keys) {
        if(empty($this->_body['post'])) {
            throw new \OutOfRangeException('post is empty');
        }

        if(count($keys)) {
            $result = array_intersect_key($this->_body['post'], array_flip($keys));
            if(count($result) != count($keys)) {
                throw new \OutOfRangeException('no such post keys');
            }
            return array_intersect_key($this->_body['post'], array_flip($keys));
        }

        return $this->_body['post'];
    }

    public function body()
    {
        return $this->_body;
    }

    public function getPath()
    {
        return $this->_uri;
    }

    public function getScheme()
    {
        return null;
    }

    public function getHost()
    {
        return null;
    }

    public function getPort()
    {
        return null;
    }

    public function getUser()
    {
        return null;
    }

    public function getPass()
    {
        return null;
    }
    public function getFragment()
    {
        return null;
    }
}