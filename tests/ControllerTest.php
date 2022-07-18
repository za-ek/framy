<?php
use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    public function testRoutes()
    {
        $app = new \Zaek\Framy\App([
            'routes' => [
                'CLI /cb' => function ($route) {
                    return new \Zaek\Framy\Action\CbFunction(function($app) {
                        echo 'Hello world!';
                    });
                },
            ]
        ]);

        // Cli GET request
        $_SERVER['argv'][1] = '/cb';
        $this->expectOutputString('Hello world!');
        $app->handle();
        $app->response()->flush();
    }
    public function testWebGetRoute()
    {
        $app = new \Zaek\Framy\App([
            'routes' => [
                'GET /cb' => function ($route) {
                    return new \Zaek\Framy\Action\CbFunction(function($app) {
                        echo 'Hello world!';
                    });
                },
            ]
        ]);

        // Web GET request
        $app->setRequest(new \Zaek\Framy\Request\Web('GET', '/cb'));
        $app->setResponse(new \Zaek\Framy\Response\Web());
        $this->expectOutputString('Hello world!');
        $app->handle();
        $app->response()->flush();
    }
}