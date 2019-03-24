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
    public function testMultipleRoute()
    {
        $app = new \Zaek\Framy\Application(
            new \Zaek\Framy\Controller()
        );
        $router = new \Zaek\Framy\Routing\Router();

        $router->addRoute('GET|POST /cb', '/index');
        $router->addRoute('OPTIONS /cb', '/options');

        // URL is defined. Methods are multiples in one
        $action = $router->getRequestAction('GET', '/cb');
        $this->assertEquals('/index', $action->getPath());
        $action = $router->getRequestAction('POST', '/cb');
        $this->assertEquals('/index', $action->getPath());

        // URL is defined. Method is defined
        $action = $router->getRequestAction('OPTIONS', '/cb');
        $this->assertEquals('/options', $action->getPath());

        // URL is defined. Method is not defined
        $this->expectException(\Zaek\Framy\Routing\NoRoute::class);
        $router->getRequestAction('PUT', '/cb');
    }
}
