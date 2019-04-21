<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Application;
use Zaek\Framy\Request\Request;
use Zaek\Framy\Response\Response;

interface Action
{
    /**
     * @param Application $app
     * @return mixed
     * @throws NotFound
     */
    public function execute(Application $app);

    /**
     * @return Request
     */
    public function getRequest() : Request;
    public function setRequest(Request $request) : void;

    /**
     * @return Response
     */
    public function getResponse() : Response;
    public function setResponse(Response $response) : void;
}