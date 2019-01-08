<?php
namespace Zaek\Framy;

class Application
{
    private $exec_file = '';
    private $controller;
    private $result;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function runFile($file)
    {
        include $file;
    }

    /**
     * @param $action
     * @return false|mixed|string
     */
    public function execute($action)
    {
        $result = null;

        ob_start(null, null, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        if(is_string($action)) {
            if(substr($action, 0, 1) === '@') {
                $file = substr($action, 1);
            } else {
                $file = $this->controller->getRootDir() . $action;
            }
            if(file_exists($file)) {
                $result = include $file;
            } else {
                $result = $this->controller->getResponse()->showError(500);
            }
        } else if (is_array($action)) {
            if(count($action) == 1) $action = $action[0];
            $result = call_user_func($action, $this);
        } else if (is_object($action) && $action instanceof Action) {
            $result = $action->execute($this);
        } else if (is_callable($action)) {
            $result = $action($this);
        }

        $buffer = ob_get_contents();
        ob_end_clean();

        $this->result = [
            'result' => $result,
            'output' => $buffer,
        ];

        return $this->result;
    }
}