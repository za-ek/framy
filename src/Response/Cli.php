<?php
namespace Zaek\Framy\Response;

class Cli extends Response
{
    public function showError($errorCode)
    {
        echo "Error: {$errorCode}\n";
    }

    public function flush()
    {
        echo $this->output;
        /*
        if(!is_null($this->result)) {
            echo "Application result:\n";
            echo "--------------------\n";
            if(is_string($this->result)) {
                echo $this->result;
            } else {
                print_r($this->result);
            }
            echo "\n--------------------\n";
            echo "\n";
        }
        if(!is_null($this->output)) {
            echo "Application output:\n";
            echo "--------------------\n";
            if(is_string($this->output)) {
                echo $this->output;
            } else {
                print_r($this->output);
            }
            echo "\n--------------------\n";
        }
        */
    }
}