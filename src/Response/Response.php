<?php
namespace Zaek\Framy\Response;

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
     */
    public function showError($errorCode) : void
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

    public function getResult()
    {
        return $this->result;
    }
    public function getOutput()
    {
        return $this->output;
    }
    /**
     * @return mixed
     */
    abstract public function flush();

    public function __toString(): string
    {
        return $this->output ?? '';
    }
}