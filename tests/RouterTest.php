<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class testRouteClass__o {
    public function testFunc() {
        return 'qwe';
    }
}

final class RouterTest extends TestCase
{
    public function testRouterConstruct()
    {
        // GET / => no route
        $router = new \Zaek\Framy\Routing\Router();
        $this->expectException(\Zaek\Framy\Routing\NoRoute::class);
        $router->getRequestAction('GET', '/');
    }
    public function testRouterAddStaticRoute()
    {
        // GET / => /index.php
        $router = new \Zaek\Framy\Routing\Router();
        $router->addRoute('GET /', '/index.php');
        $action = $router->getRequestAction('GET', '/');
        $this->assertEquals('/index.php', $action->getPath());
    }
    public function testRouterAddDynamicRoute()
    {
        $router = new \Zaek\Framy\Routing\Router();

        // GET /{id}
        $router->addRoute('GET /<id:[\d]+>', '/index/$id.html');
        $action = $router->getRequestAction('GET', '/23');
        $this->assertEquals('/index/23.html', $action->getPath());

        // No GET /{string}
        $this->expectException(\Zaek\Framy\Routing\NoRoute::class);
        $router->getRequestAction('GET', '/abc');

        // GET /{string_id}
        $router->addRoute('GET /<word:[\w]+>', '/index/$id.html');
        $action = $router->getRequestAction('GET', '/abc');
        $this->assertEquals('/index/abc.html', $action->getPath());
    }
    public function testRouterCallbackAction()
    {
        $app = new \Zaek\Framy\Application(
            new \Zaek\Framy\Controller()
        );
        $router = new \Zaek\Framy\Routing\Router();

        // function () {}
        $router->addRoute('GET /cb', function() {
            return 'qwerty';
        });
        $action = $router->getRequestAction('GET', '/cb');
        $this->assertEquals('qwerty', $action->execute($app));

        // function_name
        function testRunReturnAsdfgh () {
            return 'asdfgh';
        }
        $router->addRoute('GET /fn', 'testRunReturnAsdfgh');
        $action = $router->getRequestAction('GET', '/fn');
        $this->assertEquals('asdfgh', $action->execute($app));

        // ['class', 'method']
        $router->addRoute('GET /cm', ['testRouteClass__o', 'testFunc']);
        $action = $router->getRequestAction('GET', '/cm');
        $this->assertEquals('qwe', $action->execute($app));
    }
    public function testRouterAbsPath()
    {
        $app = new \Zaek\Framy\Application(
            new \Zaek\Framy\Controller()
        );
        $file = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, '<?php return "asd";?>');
        $router = new \Zaek\Framy\Routing\Router();
        $router->addRoute('GET /', '@' . $file);
        $action = $router->getRequestAction('GET', '/');
        $this->assertEquals('asd', $action->execute($app));
        unlink($file);
    }
}
