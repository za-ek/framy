<?php
use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    public function testRoutes()
    {
        $application = new \Zaek\Framy\Application([
            'routes' => [
                'CLI /cb' => function ($app) {
                    echo 'Hello world!';
                },
            ]
        ]);

        // Cli GET request
        $_SERVER['argv'][1] = '/cb';
        $this->expectOutputString('Hello world!');
        $application->handle();
        $application->response()->flush();
    }
    public function testWebGetRoute()
    {
        $application = new \Zaek\Framy\Application([
            'routes' => [
                'GET /cb' => function ($app) {
                    echo 'Hello world!';
                },
            ]
        ]);

        // Web GET request
        $application->setRequest(new \Zaek\Framy\Request\Web('GET', '/cb'));
        $application->setResponse(new \Zaek\Framy\Response\Web());
        $this->expectOutputString('Hello world!');
        $application->handle();
        $application->response()->flush();
    }
}