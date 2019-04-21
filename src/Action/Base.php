<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Request\Request;
use Zaek\Framy\Response\Response;

abstract class Base implements Action
{
    /**
     * @var Response
     */
    private $_response;
    /**
     * @var Request
     */
    private $_request;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request) : void
    {
        $this->_request = $request;
    }

    public function getRequest() : Request
    {
        return $this->_request;
    }

    /**
     * @return Response
     */
    public function getResponse() : Response
    {
        return $this->_response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->_response = $response;
    }
}