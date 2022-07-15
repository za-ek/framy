<?php
namespace Zaek\Framy\Routing;

use Zaek\Framy\Action\Action;

class StaticRoute extends Route {
    public function matches($request) : bool
    {
        return Methods::overlaps($request->getMethod(), $this->_config['method'])
            && $this->_config['path'] === $request->getPath();
    }

    public function getAction($request) : Action
    {
        $action = $this->convertToAction();
        $action->setRequest($request);
        return $action;
    }
}