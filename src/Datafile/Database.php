<?php
namespace Zaek\Framy\Datafile;

class Database
{
    /**
     * @var [Table]
     */
    private $tables = [];

    private $cfg;

    /**
     * Database constructor.
     * @param array $cfg
     */
    public function __construct($cfg = [])
    {
        $this->cfg = $cfg;
    }

    /**
     * @param string $name
     * @return Table
     */
    public function table(string $name)
    {
        if(empty($this->tables[$name])) {
            $this->tables[$name] = new Table($this, $name);
        }

        return $this->tables[$name];
    }

    /**
     * @return mixed
     */
    public function getDataDirectory()
    {
        return $this->cfg['dataDir'];
    }
}