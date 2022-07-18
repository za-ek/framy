<?php
namespace Zaek\Framy\Routing;

use Zaek\Framy\Action\Action;
use Zaek\Framy\Request\Request;

class DynamicRoute extends Route {
    public function __construct($config, $sort = 1)
    {
        parent::__construct($config, $sort);
    }

    public function matches(Request $request) : bool
    {
        return Methods::overlaps($request->getMethod(), $this->_config['method'])
            && preg_match_all($this->_config['path'], $request->getPath());
    }

    public function getAction($request) : Action
    {
        preg_match_all($this->_config['path'], $request->getPath(), $matches);

        foreach($this->_config['vars'] as $var) {
            if(!is_callable($this->_config['target']) && !is_object($this->_config['target'])) {
                $this->_config['target'] = str_replace(
                    '$' . $var,
                    $matches[$var][0],
                    $this->_config['target']
                );
            }
            $request->addQuery($var, $matches[$var][0]);
        }

        $action = $this->convertToAction();
        $action->setRequest($request);
        return $action;
    }
}