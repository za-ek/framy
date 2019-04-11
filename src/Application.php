<?php
namespace Zaek\Framy;

use Zaek\Framy\Action\Action;
use Zaek\Framy\Action\NotFound;

class Application
{
    private $exec_file = '';
    private $controller;
    private $result;
    private $action;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param $file
     * @return mixed
     */
    public function runFile($file)
    {
        return include $file;
    }

    /**
     * @param Action $action
     * @return false|mixed|string
     */
    public function execute(Action $action)
    {
        $this->action = $action;

        ob_start(null, null, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        try {
            $result = $action->execute($this);
        } catch (NotFound $e) {
            $this->getController()->getResponse()->showError(404);
        }

        $buffer = ob_get_contents();
        ob_end_clean();

        $this->result = [
            'result' => $result,
            'output' => $buffer,
        ];

        return $this->result;
    }

    public function getAction() : Action
    {
        return $this->action;
    }
}