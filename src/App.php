<?php
namespace Zaek\Framy;

use Zaek\Framy\Action\NotFound;
use Zaek\Framy\Datafile\Database;
use Zaek\Framy\Request\Cli;
use Zaek\Framy\Request\InvalidRequest;
use Zaek\Framy\Request\Request;
use Zaek\Framy\Request\Web;
use Zaek\Framy\Response as Response;
use Zaek\Framy\Routing\NoRoute;
use Zaek\Framy\Routing\Router;

class App
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
    public function request() : Request
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
                throw new InvalidRequest('No request');
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
    public function response() : Response\Response
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
    public function router() : Router
    {
        if(empty($this->router)) {
            $this->router = new Router(!empty($this->cfg['routes']) ? $this->cfg['routes'] : []);
        }

        return $this->router;
    }

    public function action()
    {
        return $this->_action;
    }

    /**
     * @throws Routing\InvalidRoute
     * @throws InvalidRequest
     */
    public function handle(): static
    {
        try {
            $result = null;
            $action = $this->router()->getRequestAction($this->request());
            $this->_action = $action;

            ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

            try {
                $result = $action->execute($this);
            } catch (NotFound $e) {
                $this->response()->showError(404);
            }

            $buffer = ob_get_contents();
            ob_end_clean();

            $this->response()->setOutput($buffer);
            $this->response()->setResult($result);

        } catch (NoRoute $exception) {
            $this->response()->showError(404);
        }

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getRootDir()
    {
        if(!empty($this->cfg['homeDir'])) {
            return $this->cfg['homeDir'];
        }

        throw new \Error('Missing home dir');
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
    public function user()
    {
        if(is_null($this->user)) {
            $this->user = new User($this);
        }

        return $this->user;
    }

    public function conf($key)
    {
        if(!array_key_exists($key, $this->cfg)) {
            throw new InvalidConfiguration($key);
        }

        return $this->cfg[$key];
    }

    public function setConf($k, $v) : static
    {
        $this->cfg[$k] = $v;

        return $this;
    }

    public function confDefined($key) : bool
    {
        return array_key_exists($key, $this->cfg);
    }

    public function runFile($file)
    {
        return include $file;
    }
}