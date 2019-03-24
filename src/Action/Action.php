<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Application;
use Zaek\Framy\Response\Response;

interface Action
{
    /**
     * @param Application $app
     * @return mixed
     * @throws NotFound
     */
    public function execute(Application $app);

    public function setMethod(string $method) : void;
    public function setUri(string $uri) : void;
    public function getMethod() : string;
    public function getUri() : string;
    public function setResponse(Response $response) : void;
    public function getResponse();
}