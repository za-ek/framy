<?php
namespace Zaek\Framy\Action;

use Zaek\Framy\Application;

interface Action
{
    /**
     * @param Application $app
     * @return mixed
     * @throws NotFound
     */
    public function execute(Application $app);
}