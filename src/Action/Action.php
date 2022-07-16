<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\App;
use Zaek\Framy\Request\Request;
use Zaek\Framy\Response\Response;

interface Action
{
    /**
     * @param App $app
     * @return mixed
     * @throws NotFound
     */
    public function execute(App $app);

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