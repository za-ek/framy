<?php
use PHPUnit\Framework\TestCase;

class CustomRequest extends Zaek\Framy\Request\Web {
    private $_method;
    private $_uri;
    public function __construct($_method, $_uri)
    {
        $this->_method = $_method;
        $this->_uri = $_uri;
    }

    public function getMethod()
    {
        return $this->_method;
    }
    public function getUri()
    {
        return $this->_uri;
    }
}

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
        $controller->setRequest(new CustomRequest('GET', '/cb'));
        $controller->setResponse(new \Zaek\Framy\Response\Web());
        $this->expectOutputString('Hello world!');
        $controller->handle();
        $controller->getResponse()->flush();
    }
}