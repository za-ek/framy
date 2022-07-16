<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Zaek\Framy\Action\StaticFile;
use Zaek\Framy\Application;
use Zaek\Framy\Request\Web as WebRequest;
use Zaek\Framy\Request\Cli as CliRequest;
use Zaek\Framy\Response\Web as WebResponse;
use Zaek\Framy\Response\Json as JsonResponse;
use Zaek\Framy\Routing\Router;
use Zaek\Framy\Routing\NoRoute;

class testRouteClass__o {
    public static function testFunc() {
        return 'qwe';
    }
}

final class RouterTest extends TestCase
{
    public function testRouterConstruct()
    {
        // GET / => no route
        $router = new Router();
        $this->expectException(NoRoute::class);
        $router->getRequestAction(new WebRequest('GET', '/'));
    }

    public function testRouterAddStaticRoute()
    {
        // GET / => /index.php
        $router = new Router();
        $router->addRoute('GET /', '/index.php');
        $action = $router->getRequestAction(new WebRequest('GET', '/'));
        $this->assertEquals('/index.php', $action->getPath());
    }

    public function testRouterAddDynamicRoute()
    {
        $router = new Router();

        // GET /{id}
        $router->addRoute('GET /<id:[\d]+>', '/index/$id.html');
        $action = $router->getRequestAction(new WebRequest('GET', '/23'));
        $this->assertEquals('/index/23.html', $action->getPath());

        // No GET /{string}
        $this->expectException(NoRoute::class);
        $router->getRequestAction(new WebRequest('GET', '/abc'));

        // GET /{string_id}
        $router->addRoute('GET /<word:[\w]+>', '/index/$id.html');
        $action = $router->getRequestAction(new WebRequest('GET', '/abc'));
        $this->assertEquals('/index/abc.html', $action->getPath());
    }

    public function testRouterCallbackAction()
    {
        $application = new \Zaek\Framy\Application();
        // function () {}
        $application->router()->addRoute('GET /cb', function () {
            return 'qwerty';
        });
        $application->setRequest(new WebRequest('GET', '/cb'));
        $this->assertEquals('qwerty', $application->handle()->response()->getResult());

        // function_name
        function testRunReturnAsdfgh()
        {
            return 'asdfgh';
        }

        $application->router()->addRoute('GET /fn', 'testRunReturnAsdfgh');
        $application->setRequest(new WebRequest('GET', '/fn'));
        $this->assertEquals('asdfgh', $application->handle()->response()->getResult());

        // ['class', 'method']
        $application->router()->addRoute('GET /cm', ['testRouteClass__o', 'testFunc']);
        $application->setRequest(new WebRequest('GET', '/cm'));
        $this->assertEquals('qwe', $application->handle()->response()->getResult());
    }

    public function testRouterAbsPath()
    {
        $application = new \Zaek\Framy\Application();
        $file = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, '<?php return "asd";?>');
        $application->router()->addRoute('GET /', '@' . $file);
        $application->setRequest(new WebRequest('GET', '/'));
        $this->assertEquals('asd', $application->handle()->response()->getResult());
        unlink($file);
    }

    public function testMultipleRoute()
    {
        $router = new Router();

        $router->addRoute('GET|POST /cb', '/index');
        $router->addRoute('OPTIONS /cb', '/options');

        // URL is defined. Methods are multiples in one
        $action = $router->getRequestAction(new WebRequest('GET', '/cb'));
        $this->assertEquals('/index', $action->getPath());
        $action = $router->getRequestAction(new WebRequest('POST', '/cb'));
        $this->assertEquals('/index', $action->getPath());

        // URL is defined. Method is defined
        $action = $router->getRequestAction(new WebRequest('OPTIONS', '/cb'));
        $this->assertEquals('/options', $action->getPath());

        // URL is defined. Method is not defined
        $this->expectException(NoRoute::class);
        $router->getRequestAction(new WebRequest('PUT', '/cb'));
    }

    public function testRestMethods()
    {
        $router = new Router();

        $router->addRoute('REST /users', '/users');

        $action = $router->getRequestAction(new WebRequest('GET', '/users'));
        $this->assertEquals('/users/List.php', $action->getPath());

        $action = $router->getRequestAction(new WebRequest('POST', '/users'));
        $this->assertEquals('/users/Add.php', $action->getPath());

        $action = $router->getRequestAction(new WebRequest('GET', '/users/32'));
        $this->assertEquals('/users/Item.php', $action->getPath());

        $action = $router->getRequestAction(new WebRequest('PATCH', '/users/32'));
        $this->assertEquals('/users/Update.php', $action->getPath());

        $action = $router->getRequestAction(new WebRequest('DELETE', '/users/32'));
        $this->assertEquals('/users/Delete.php', $action->getPath());
    }

    public function testResponse()
    {
        $router = new Router();
        $router->addRoute('GET:html|POST:json|CLI /response', '/users/Update.php');
        $router->addRoute('GET:json /response/<id:\d+>', '/users/Update.php');

        $action = $router->getRequestAction(new WebRequest('POST', '/response'));
        $this->assertEquals(JsonResponse::class, get_class($action->getResponse()));

        $action = $router->getRequestAction(new WebRequest('GET', '/response'));
        $this->assertEquals(WebResponse::class, get_class($action->getResponse()));;

        $action = $router->getRequestAction(new WebRequest('GET', '/response/2'));
        $this->assertEquals(JsonResponse::class, get_class($action->getResponse()));
    }

    public function testVars()
    {
        $router = new Router();
        $router->addRoute('GET /response/<id:\d+>/<code:\d+>/', '/users/Update.php');
        $router->addRoute('GET /response/<id:\d+>/', '/users/Update.php');

        $request = new WebRequest('GET', '/response/2/5/');
        $action = $router->getRequestAction($request);
        $this->assertEquals(['code' => '5'], $action->getRequest()->getQueries('code'));
        $action = $router->getRequestAction(new WebRequest('GET', '/response/2/'));
        $this->assertEquals(['id' => '2'], $action->getRequest()->getQueries('id'));
    }

    public function testGet()
    {
        $router = new Router();
        $router->addRoute('GET /response/', '/users/Update.php');
        $router->addRoute('GET /response/<code:[\w\d]+>/', '/users/Update.php');
        $router->addRoute('CLI /response/', '/users/Update.php');

        $action = $router->getRequestAction(new WebRequest('GET', '/response/?code=test'));
        $this->assertEquals(['code' => 'test'], $action->getRequest()->getQueries('code'));
        $action = $router->getRequestAction(new CliRequest('/response/?code=test'));
        $this->assertEquals(['code' => 'test'], $action->getRequest()->getQueries('code'));
        $action = $router->getRequestAction(new WebRequest('GET', '/response/testing1/?subCode=test'));
        $this->assertEquals(['code' => 'testing1', 'subCode' => 'test'], $action->getRequest()->getQueries('code', 'subCode'));
    }

    public function testWildcardRouter()
    {
        $application = new Application();

        // WEB .* => /index.php
        $application->router()->addRoute('WEB /zaek/admin/zaek_admin', function () {
            return "static";
        });
        $application->router()->addRoute('WEB /<path:.*>', function (Application $application) {
            // Возвращает путь, в реальности исполняет его
            return '/index.php';
        });
        $application->setRequest(new WebRequest('GET', '/zaek/admin/'));
        $this->assertEquals('/index.php', $application->handle()->response()->getResult());

        $application->setRequest(new WebRequest('GET', '/zaek/admin/zaek_admin'));
        $this->assertEquals('static', $application->handle()->response()->getResult());
    }

    public function testStaticFile()
    {
        $application = new Application(['homeDir' => sys_get_temp_dir()]);

        $static_path = tempnam($application->getRootDir(), 'zt');
        $relative_path = substr($static_path, strlen($application->getRootDir()));
        $this->assertFileExists($static_path);

        $content = bin2hex(random_bytes(10));
        file_put_contents($static_path, $content);

        // WEB .* => /index.php
        $application->router()->addRoute('GET ' . $relative_path, new StaticFile());
        $application->setRequest(new WebRequest('GET', $relative_path));
        $this->assertEquals($content, $application->handle()->response()->getOutput());

        unlink($static_path);
    }
}
