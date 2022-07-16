<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\App;

class CbFunction extends Base
{
    private $_cb;

    public function __construct(callable $cb)
    {
        $this->_cb = $cb;
    }

    /**
     * @param App $app
     * @return mixed
     * @throws NotFound
     */
    public function execute(App $app)
    {
        return call_user_func($this->_cb, $app);
    }
}