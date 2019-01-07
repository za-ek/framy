<?php
namespace Zaek\Framy;

use Filebase\Database;
use Zaek\Framy\Request\Cli;
use Zaek\Framy\Request\Request;
use Zaek\Framy\Request\Web;
use Zaek\Framy\Response as Response;
use Zaek\Framy\Routing\Router;

class Controller
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    private $cfg = [];

    /**
     * @var Request
     */
    private $request = null;

    /**
     * @var Response\Response
     */
    private $response = null;

    /**
     * Controller constructor.
     * @param array $cfg
     */
    public function __construct(array $cfg = [])
    {
        foreach($cfg as $k => $v) {
            if (isset($this->cfg[$k]) && is_array($this->cfg[$k])) {
                $this->cfg[$k] = array_replace_recursive($this->cfg[$k], $v);
            } else {
                $this->cfg[$k] = $v;
            }
        }
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request) : void
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest() : Request
    {
        if(is_null($this->request)) {
            $this->request = (php_sapi_name() == 'cli') ? new Cli() : new Web();
        }

        return $this->request;
    }

    /**
     * @param Response\Response $response
     */
    public function setResponse(Response\Response $response) : void
    {
        $this->response = $response;
    }

    /**
     * @return Response\Response
     */
    public function getResponse() : Response\Response
    {
        if(is_null($this->response)) {
            $this->response = (php_sapi_name() == 'cli') ? new Response\Cli() : new Response\Web();
        }

        return $this->response;
    }

    /**
     * @param Router $router
     */
    public function setRouter(Router $router) : void
    {
        $this->router = $router;
    }

    /**
     * @return Router
     * @throws Routing\InvalidRoute
     */
    public function getRouter() : Router
    {
        if(empty($this->router)) {
            $this->router = new Router(!empty($this->cfg['routes']) ? $this->cfg['routes'] : []);
        }

        return $this->router;
    }

    /**
     * @throws Routing\InvalidRoute
     */
    public function handle() : void
    {
        $action = $this->getRouter()->getRequestAction(
            $this->getRequest()->getMethod(),
            $this->getRequest()->getUri()
        );

        if($action) {
            $app = new Application($this);
            $execResult = $app->execute($action);
            $this->getResponse()->setOutput($execResult['output']);
            $this->getResponse()->setResult($execResult['result']);
        } else {
            $this->getResponse()->showError(404);
        }
    }

    /**
     * @return mixed|string
     */
    public function getRootDir()
    {
        if(!empty($this->cfg['homeDir'])) {
            return $this->cfg['homeDir'];
        }

        return (!empty($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : '';
    }
}