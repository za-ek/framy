<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Application;

class CbFunction extends Base
{
    private $_cb;

    public function __construct(callable $cb)
    {
        $this->_cb = $cb;
    }

    /**
     * @param Application $application
     * @return mixed
     * @throws NotFound
     */
    public function execute(Application $application)
    {
        return call_user_func($this->_cb, $application);
    }
}