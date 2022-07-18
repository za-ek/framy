<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\App;

class Json extends Base
{
    private mixed $_data;

    public function __construct($data)
    {
        $this->_data = $data;
    }
    public function execute(App $app)
    {
        $app->response()->setOutput(json_encode($this->_data));
    }
}