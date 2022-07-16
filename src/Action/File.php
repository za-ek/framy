<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\App;

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
     * @param App $app
     * @return mixed
     * @throws NotFound
     */
    public function execute(App $app)
    {
        $abs = $this->getAbsolutePath($app);

        if(file_exists($abs)) {
            return $app->runFile($abs);
        }

        throw new NotFound;
    }

    public function getPath(): string
    {
        return $this->_path;
    }

    public function getAbsolutePath(App $app): string
    {
        if(substr($this->_path, 0, 1) === '@') {
            return substr($this->_path, 1);
        } else {
            return $app->getRootDir() . $this->_path;
        }
    }
}