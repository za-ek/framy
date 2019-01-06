<?php
include 'lib/Routing/InvalidRoute.php';
include 'lib/Routing/Router.php';
include 'lib/Request.php';

class Controller
{
    public function run()
    {
        $request = new \Zaek\Request();
        try {
            $router = new \zaek\Routing\Router(include '.router.php');
            $action = $router->getRequestAction($request->getMethod(), $request->getUri());
            if($action) {
                if(is_string($action)) {
                    include 'http'. $action;
                }
            } else {
                include '404.php';
            }
        } catch (\Zaek\Routing\InvalidRoute $e) {
            echo $e->getMessage() . '<br/>';
        }
    }
}

(new Controller())->run();