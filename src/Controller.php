<?php
namespace Zaek\Framy;

use Zaek\Framy\Datafile\Database;
use Zaek\Framy\Request\Cli;
use Zaek\Framy\Request\Request;
use Zaek\Framy\Request\InvalidRequest as InvalidRequest;
use Zaek\Framy\Request\Web;
use Zaek\Framy\Response as Response;
use Zaek\Framy\Routing\NoRoute;
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
     * @var Database
     */
    private $db = null;
    /**
     * @var User
     */
    private $user = null;
    /**
     * @var \Zaek\Framy\Action\Action|null
     */
    private $_action = null;
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

        if(!isset($cfg['useDefault']) || !$cfg['useDefault']) {
            if(empty($this->cfg['routes'])) $this->cfg['routes'] = [];
            $dir = '@' . __DIR__.'/../bin';

            $this->cfg['routes']['POST /framy/signUp'] = [$dir.'/SignUp.php'];
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
     * @throws InvalidRequest
     */
    public function getRequest() : Request
    {
        if(is_null($this->request)) {
            if(php_sapi_name() == 'cli') {
                $this->request = new Cli();
                if($this->request->getArgument('use-request')) {
                    $this->request = new Web(
                        $this->request->getArgument('use-request'),
                        $this->request->getPath()
                    );
                }
            } else {
                $this->request = new Web();
            }
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
            if(!empty($this->_action)) {
                if($this->_action->getResponse()) {
                    $this->response = $this->_action->getResponse();
                }
            }
        }

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
     * @throws InvalidRequest
     */
    public function handle() : void
    {
        try {
            $action = $this->getRouter()->getRequestAction($this->getRequest());
            $this->_action = $action;

            $app = new Application($this);
            $execResult = $app->execute($action);
            $this->getResponse()->setOutput($execResult['output']);
            $this->getResponse()->setResult($execResult['result']);

        } catch (NoRoute $exception) {
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

    /**
     * @return Database
     */
    public function db()
    {
        if(is_null($this->db)) {
            $this->db = new Database($this->cfg);
        }

        return $this->db;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        if(is_null($this->user)) {
            $this->user = new User($this);
        }

        return $this->user;
    }

    public function getConf($key)
    {
        if(!array_key_exists($key, $this->cfg)) {
            throw new InvalidConfiguration($key);
        }

        return $this->cfg[$key];
    }
}