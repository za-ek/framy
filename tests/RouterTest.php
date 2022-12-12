<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Zaek\Framy\Action\CbFunction;
use Zaek\Framy\Action\File;
use Zaek\Framy\Action\StaticFile;
use Zaek\Framy\App;
use Zaek\Framy\Request\Web as WebRequest;
use Zaek\Framy\Request\Cli as CliRequest;
use Zaek\Framy\Response\Web as WebResponse;
use Zaek\Framy\Response\Json as JsonResponse;
use Zaek\Framy\Routing\Route;
use Zaek\Framy\Routing\RoutePrefix;
use Zaek\Framy\Routing\RouteGroup;
use Zaek\Framy\Routing\RouteProxy;
use Zaek\Framy\Routing\Router;
use Zaek\Framy\Routing\NoRoute;

class testRouteClass__o {
    public static function testFunc() {
        return new CbFunction(function () {
            return 'qwe';
        });
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
        $app = new \Zaek\Framy\App();
        // function () {}
        $app->router()->addRoute('GET /cb', new CbFunction(function () {
            return 'qwerty';
        }));
        $app->setRequest(new WebRequest('GET', '/cb'));
        $this->assertEquals('qwerty', $app->handle()->response()->getResult());

        // function_name
        function testRunReturnAsdfgh()
        {
            return new CbFunction(function() {return 'asdfgh';});
        }

        $app->router()->addRoute('GET /fn', 'testRunReturnAsdfgh');
        $app->setRequest(new WebRequest('GET', '/fn'));
        $this->assertEquals('asdfgh', $app->handle()->response()->getResult());

        // ['class', 'method']
        $app->router()->addRoute('GET /cm', ['testRouteClass__o', 'testFunc']);
        $app->setRequest(new WebRequest('GET', '/cm'));
        $this->assertEquals('qwe', $app->handle()->response()->getResult());
    }

    public function testRouterAbsPath()
    {
        $app = new \Zaek\Framy\App();
        $file = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, '<?php return "asd";?>');
        $app->router()->addRoute('GET /', '@' . $file);
        $app->setRequest(new WebRequest('GET', '/'));
        $this->assertEquals('asd', $app->handle()->response()->getResult());
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

        $action = $router->getRequestAction(new WebRequest('GET', '/users/32'));
        $this->assertEquals(32, $action->getRequest()->getQuery('id'));
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
        $app = new App(['homeDir' => '/']);

        // WEB .* => /index.php
        $app->router()->addRoute('WEB /zaek/admin/zaek_admin', new CbFunction(function () {return "static";}));
        $app->router()->addRoute('WEB /<path:.*>', new CbFunction(function (App $app) {
            // Возвращает путь, в реальности исполняет его
            return '/index.php';
        }));
        $app->setRequest(new WebRequest('GET', '/zaek/admin/'));
        $this->assertEquals('/index.php', $app->handle()->response()->getResult());

        $app->setRequest(new WebRequest('GET', '/zaek/admin/zaek_admin'));
        $this->assertEquals('static', $app->handle()->response()->getResult());
    }

    public function testStaticFile()
    {
        $app = new App(['homeDir' => sys_get_temp_dir()]);

        $static_path = tempnam($app->getRootDir(), 'zt');
        $relative_path = substr($static_path, strlen($app->getRootDir()));
        $this->assertFileExists($static_path);

        $content = bin2hex(random_bytes(10));
        file_put_contents($static_path, $content);

        // WEB .* => /index.php
        $app->router()->addRoute('GET ' . $relative_path, new StaticFile());
        $app->setRequest(new WebRequest('GET', $relative_path));
        $this->assertEquals($content, $app->handle()->response()->getOutput());

        unlink($static_path);
    }

    public function testWebDeleteMethod()
    {
        $router = new Router();
        $router->addRoute('DELETE|WEB /response/<all:.*>', '/users/Update.php');

        $action = $router->getRequestAction(new WebRequest('DELETE', '/response/hey'));
        $this->assertEquals('/users/Update.php', $action->getPath());
    }

    public function testPrefix()
    {
        $router = new Router([
            ...new RoutePrefix('/api', [
                'POST /login' => '/api/login.php',
                'POST /registration' => '/api/registration.php',
                new RoutePrefix('/catalog', [
                    new RoutePrefix('/goods', [
                        'GET|POST|DELETE|OPTIONS /<id:[\d]+>' => '/api/catalog/good.php',
                        'GET /' => '/api/catalog/goods.php',
                    ]),
                    new RoutePrefix('/settings', [
                        'GET /' => '/api/catalog/settings.php'
                    ])
                ])
            ]),
            '/' => '//',
        ]);
        $router->addRoute('GET /catalog', '/api/index.php', 'api');
//
        $action = $router->getRequestAction(new WebRequest('DELETE', '/api/catalog/goods/12'));
        $this->assertEquals('/api/catalog/good.php', $action->getPath());

        $this->expectException(NoRoute::class);
        $router->getRequestAction(new WebRequest('POST', '/api/catalog/settings/'));

        $action = $router->getRequestAction(new WebRequest('GET', '/api/catalog/settings/'));
        $this->assertEquals('/api/catalog/settings.php', $action->getPath());

        $action = $router->getRequestAction(new WebRequest('GET', '/'));
        $this->assertEquals('//', $action->getPath());
    }

    public function testVersioning()
    {
        $routes = new RoutePrefix('/users', ['GET /' => '/users.php']);

        $v1 = new RoutePrefix('/v1', ['GET /version' => '/v1.php', ...$routes]);
        $v2 = new RoutePrefix('/v2', ['GET /version' => '/v2.php', ...$routes]);
        $v3 = new RoutePrefix('/v3', ['GET /version' => '/v3.php', ...$routes, ...['GET /users/' => '/users_change.php']]);

        $router = new Router(new RoutePrefix('/api', [
            ...$v1,
            ...$v2,
            ...$v3,
            ...$routes,
        ]));

        $map = [
            'GET /api/users/' => '/users.php',
            'GET /api/v1/version' => '/v1.php',
            'GET /api/v1/users/' => '/users.php',
            'GET /api/v2/version' => '/v2.php',
            'GET /api/v2/users/' => '/users.php',
            'GET /api/v3/version' => '/v3.php',
            'GET /api/v3/users/' => '/users_change.php',
        ];

        foreach($map as $a => $b) {
            try {
                $action = $router->getRequestAction(new WebRequest(...explode(' ', $a)));
                $this->assertEquals($b, $action->getPath());
            } catch (NoRoute $e) {
                $this->fail('No route for ' . $a);
            }
        }
    }

    public function testDynamicRest()
    {
        $router = new Router([
            'REST /users/' => '/users.php',
            'REST /users/<user_id:[\d]+>/friends' => '/friends',
        ]);

        $action = $router->getRequestAction(new WebRequest('GET', '/users/1/friends'));
        $this->assertEquals('/friends/List.php', $action->getPath());
    }
    public function testRouteProxy()
    {
        define('RANDOM_STRI', rand(0, 9999999));

        $proxyFunction = function($route) {
            return new CbFunction(function(App $app) use($route) {
                if($app->user()->getId() < 1) {
                    throw new Exception('No auth ' . RANDOM_STRI, 401);
                }

                return new File($route);
            });
        };

        $app = new App(['useDefault' => false, 'routes' => [
            'GET /' => new CbFunction(function() {echo "OK";}),
            ...new RouteProxy($proxyFunction,new RoutePrefix('/users', [
                'GET /' => '/users.php',
                'GET /<user_id:[\d]+>/friends' => '/friends',
            ])),
            ...new RoutePrefix('/data', new RouteProxy($proxyFunction, [
                'GET /catalog' => '/catalog'
            ]))
        ]]);

        $app->setRequest(new WebRequest('GET', '/users/1/friends'));
        try {
            $app->handle();
            $this->assertTrue(false);
        } catch (\Exception $e) {
            if($e->getMessage() === 'No auth ' . RANDOM_STRI)
                $this->assertTrue(true);
            else
                $this->assertTrue(false);
            ob_get_contents();
            ob_end_clean();
        }


        $app->setRequest(new WebRequest('GET', '/data/catalog'));
        try {
            $app->handle();
            $this->assertTrue(false);
        } catch (\Exception $e) {
            if($e->getMessage() === 'No auth ' . RANDOM_STRI)
                $this->assertTrue(true);
            else
                $this->assertTrue(false);
            ob_get_contents();
            ob_end_clean();
        }

        $app->setRequest(new WebRequest('GET', '/data/noroute'));
        $app->handle();

        $app->setRequest(new WebRequest('GET', '/'));
        $app->handle();
        $this->assertEquals('OK', $app->response()->getOutput());
    }
}
