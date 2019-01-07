<?php
namespace Zaek\Response;

abstract class Response
{
    /**
     * @var mixed
     */
    protected $output;
    /**
     * @var mixed
     */
    protected $result;
    /**
     * @var int
     */
    protected $error;

    /**
     * @param $errorCode
     * @return mixed
     */
    public function showError($errorCode)
    {
        $this->error = $errorCode;
    }

    /**
     * @param $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @param $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    abstract public function flush();
}