<?php
namespace Zaek\Framy\Datafile;

class Field
{
    private $id;
    private $code;

    public function __construct($id, $code)
    {
        $this->id = $id;
        $this->code = $code;
    }

    public function getId()
    {
        return $this->id;
    }
    public function getCode()
    {
        return $this->code;
    }
}