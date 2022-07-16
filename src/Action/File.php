<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Application;

class File extends Base
{
    /**
     * @var string
     */
    protected $_path;

    /**
     * File constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }

    /**
     * @param Application $application
     * @return mixed
     * @throws NotFound
     */
    public function execute(Application $application)
    {
        $abs = $this->getAbsolutePath($application);

        if(file_exists($abs)) {
            return $application->runFile($abs);
        }

        throw new NotFound;
    }

    public function getPath(): string
    {
        return $this->_path;
    }

    public function getAbsolutePath(Application $application): string
    {
        if(substr($this->_path, 0, 1) === '@') {
            return substr($this->_path, 1);
        } else {
            return $application->getRootDir() . $this->_path;
        }
    }
}