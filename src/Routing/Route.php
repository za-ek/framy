<?php
namespace Zaek\Framy\Routing;

use Zaek\Framy\Action\Action;
use Zaek\Framy\Action\CbFunction;
use Zaek\Framy\Action\File;
use Zaek\Framy\Request\Request;

abstract class Route
{
    protected $_config;
    public $sort;
    public function __construct($config, $sort = 0)
    {
        $this->_config = $config;
        $this->sort = $sort;
    }
    abstract public function matches(Request $request) : bool;
    /**
    * @param int|mixed $sort
    */
    public function setSort(mixed $sort): void { $this->sort = $sort; }

    abstract public function getAction($request) : Action;

    protected function convertToAction()
    {
        $target = $this->_config['target'];

        if (is_array($target)) {
            if(is_callable($this->_config['target'])) {
                $action = new CbFunction($this->_config['target']);
            } else {
                $action = new File($this->_config['target']);
            }
        } else if (is_object($target) && $target instanceof Action) {
            $action = $target;
        } else if (is_callable($target)) {
            $action = new CbFunction($target);
        } else if (is_string($target)) {
            $action = new File($target);
        } else {
            var_dump($target);
            die();
        }

        if(!empty($this->_config['meta'])) {
            if(!empty($this->_config['meta']['response'])) {
                $className = Router::getResponseClass($this->_config['meta']['response']);
                $action->setResponse(new $className);
            }
        }

        return $action;
    }
    public function __toString()
    {
        return $this->_config['path'];
    }
}