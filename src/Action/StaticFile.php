<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Application;
use Zaek\Framy\Request\InvalidRequest;

class StaticFile extends Base
{
    /**
     * @param Application $application
     * @return mixed
     * @throws NotFound
     * @throws InvalidRequest
     */
    public function execute(Application $application) : void
    {
        $abs = $application->getRootDir() .
            $application->request()->getPath();

        if(file_exists($abs)) {
            readfile($abs);
        } else {
            throw new NotFound;
        }
    }
}