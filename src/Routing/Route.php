<?php
namespace Zaek\Framy\Routing;

use Zaek\Framy\Action\Action;
use Zaek\Framy\Action\File;
use Zaek\Framy\Action\Json;
use Zaek\Framy\Request\Request;

abstract class Route
{
    protected array $_config;
    public int $sort;
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

    /**
     * convertToAction формирует Action на основе текущего Route
     * метод может быть вызван до непосредственного исполнения
     *
     * Если target не наследует Action - он будет выполнен в момент
     *  вызова данного метода, результат выполнения - Action, будет возвращён
     *
     * Если target может быть вызван - он будет вызван непосредственно
     * Если target массив - он будет возвращён как Action\Json
     * Если target Action - он будет возвращён
     * Если target строка - она будет использована как путь для создания Action\File
     *
     * @return Action
     * @throws \Exception
     */
    protected function convertToAction() : Action
    {
        $target = $this->_config['target'];

        if (is_array($target)) {
            if(is_callable($target)) {
                $action = call_user_func($target, $this);
            } else {
                $action = new Json($target);
            }
        } else if ($target instanceof Action) {
            $action = $target;
        } else if (is_callable($target)) {
            $action = call_user_func($target, $this);
        } else if (is_string($target)) {
            $action = new File($target);
        }

        if(!($action instanceof Action)) {
            throw new \Exception('Can not create Action for current route');
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