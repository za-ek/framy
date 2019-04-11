<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Response\Response;

abstract class Base implements Action
{
    /**
     * @var string
     */
    private $_method;
    /**
     * @var string
     */
    private $_uri;
    /**
     * @var Response
     */
    private $_response;
    /**
     * @var string[][]
     */
    private $vars = [
        'GET' => [],
        'POST' => [],
    ];

    public function setMethod(string $method) : void
    {
        $this->_method = $method;
    }

    public function setUri(string $uri) : void
    {
        $this->_uri = $uri;
    }

    public function getMethod() : string
    {
        return $this->_method;
    }

    public function getUri() : string
    {
        return $this->_uri;
    }
    public function getResponse()
    {
        return $this->_response;
    }
    public function setResponse(Response $response): void
    {
        $this->_response = $response;
    }
    public function setVars($type, $vars): void
    {
        $this->vars[$type] = array_merge($this->vars[$type], $vars);
    }
    public function getVar($var)
    {
        foreach($this->vars as $type => $vars) {
            if(isset($vars[$var])) {
                return $vars[$var];
            }
        }
    }
}