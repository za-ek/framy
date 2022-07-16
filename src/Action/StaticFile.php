<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\App;
use Zaek\Framy\Request\InvalidRequest;

class StaticFile extends Base
{
    /**
     * @param App $app
     * @return mixed
     * @throws NotFound
     * @throws InvalidRequest
     */
    public function execute(App $app) : void
    {
        $abs = $app->getRootDir() .
            $app->request()->getPath();

        if(file_exists($abs)) {
            readfile($abs);
        } else {
            throw new NotFound;
        }
    }
}