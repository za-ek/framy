<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Application;

class File extends Base
{
    /**
     * @var string
     */
    private $_path;

    /**
     * File constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }

    /**
     * @param Application $app
     * @return mixed
     * @throws NotFound
     */
    public function execute(Application $app)
    {
        if(substr($this->_path, 0, 1) === '@') {
            $file = substr($this->_path, 1);
        } else {
            $file = $app->getController()->getRootDir() . $this->_path;
        }

        if(file_exists($file)) {
            return $app->runFile($file);
        }

        throw new NotFound;
    }

    public function getPath()
    {
        return $this->_path;
    }
}