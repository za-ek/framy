<?php
use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    public function testRoutes()
    {
        $controller = new \Zaek\Framy\Controller([
            'routes' => [
                'CLI /cb' => function ($app) {
                    echo 'Hello world!';
                },
            ]
        ]);

        // Cli GET request
        $_SERVER['argv'][1] = '/cb';
        $this->expectOutputString('Hello world!');
        $controller->handle();
        $controller->getResponse()->flush();
    }
    public function testWebGetRoute()
    {
        $controller = new \Zaek\Framy\Controller([
            'routes' => [
                'GET /cb' => function ($app) {
                    echo 'Hello world!';
                },
            ]
        ]);

        // Web GET request
        $controller->setRequest(new \Zaek\Framy\Request\Web('GET', '/cb'));
        $controller->setResponse(new \Zaek\Framy\Response\Web());
        $this->expectOutputString('Hello world!');
        $controller->handle();
        $controller->getResponse()->flush();
    }
}