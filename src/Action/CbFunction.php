<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Application;

class CbFunction implements Action
{
    private $_cb;

    public function __construct(callable $cb)
    {
        $this->_cb = $cb;
    }

    /**
     * @param Application $app
     * @return mixed
     * @throws NotFound
     */
    public function execute(Application $app)
    {
        return call_user_func($this->_cb, $app);
    }
}