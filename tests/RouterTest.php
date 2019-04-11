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
    public function testRestMethods()
    {
        $router = new \Zaek\Framy\Routing\Router();

        $router->addRoute('REST /users', '/users');

        $action = $router->getRequestAction('GET', '/users');
        $this->assertEquals('/users/List.php', $action->getPath());

        $action = $router->getRequestAction('POST', '/users');
        $this->assertEquals('/users/Add.php', $action->getPath());

        $action = $router->getRequestAction('GET', '/users/32');
        $this->assertEquals('/users/Item.php', $action->getPath());

        $action = $router->getRequestAction('PATCH', '/users/32');
        $this->assertEquals('/users/Update.php', $action->getPath());

        $action = $router->getRequestAction('DELETE', '/users/32');
        $this->assertEquals('/users/Delete.php', $action->getPath());
    }
    public function testResponse()
    {
        $router = new \Zaek\Framy\Routing\Router();
        $router->addRoute('GET:html|POST:json|CLI /response', '/users/Update.php');
        $router->addRoute('GET:json /response/<id:\d+>', '/users/Update.php');

        $action = $router->getRequestAction('POST', '/response');
        $this->assertEquals(\Zaek\Framy\Response\Json::class, get_class($action->getResponse()));

        $action = $router->getRequestAction('GET', '/response');
        $this->assertEquals(\Zaek\Framy\Response\Web::class, get_class($action->getResponse()));;

        $action = $router->getRequestAction('GET', '/response/2');
        $this->assertEquals(\Zaek\Framy\Response\Json::class, get_class($action->getResponse()));
    }
    public function testVars()
    {
        $router = new \Zaek\Framy\Routing\Router();
        $router->addRoute('GET /response/<id:\d+>/<code:\d+>/', '/users/Update.php');
        $router->addRoute('GET /response/<id:\d+>/', '/users/Update.php');

        $action = $router->getRequestAction('GET', '/response/2/5/');
        $this->assertEquals("5", $action->getVar('code'));
        $action = $router->getRequestAction('GET', '/response/2/');
        $this->assertEquals("2", $action->getVar('id'));
    }
}
