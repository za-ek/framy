# Framy
Simple php framework will be useful for creating provisional endpoints in frontend development.

## Start

Create entrypoint for you scripts and define configuration as mentioned in the [next part](#config)

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $application = new \Zaek\Framy\Application([
        'homeDir' => __DIR__,
        'routes' => [
            'GET /' => '/web/Index.php',
        ]
    ]);
    $application->handle();
    $application->response()->flush();

} catch (\Exception $e) {
    echo $e->getMessage();
}
```

## Routing
You can overwrite default router calling the `setRouter` method

```php
\Zaek\Framy\Application::setRouter(\Zaek\Framy\Router)
```
Router syntax:
```php
[
  '[METHOD[:RESPONSE_TYPE] ]URI' => TARGET
] 
```
##### Method
Method must be one of the following: 
`GET|HEAD|POST|PUT|DELETE|CONNECT|OPTIONS|TRACE|CLI`  
Skip the method definition mean that URI is accessible by any of knowing methods

##### Response type
You could specify response type
```
html|json
```

##### URI
URI may be static...  
```
/api/testCall
```  
...or dynamic
```
/api/<dynamicGroup>
``` 
where dynamic group defines with the expression:
```
variable:regex
variable - any latin symbol
regex - regular expression rule

Example:
/api/user<userId:[\d]+> 
(converts by framework to #/api/user(?<userId>[\d]+)#)
```

##### Target
Target can be a callback function, 
an array contains function name, 
an array with class name and method name (method must be static for class names not object),
or an object implements \Zaek\Framy\Action interface
 
##### Examples
```
[
  '/api/staticCall' => '/Web/Index.php',
  'GET:json|CLI /api/runCron' => '/Cli/Job.php',
  '/api/users/<userId:[\d]+>' => '/Web/User.php', // access to $userId from Controller::getAction()['vars']
  '/api/functionCall' => function(Controller $app) {},
  '/api/methodCall' => ['MyController', 'MethodAction'],
  '/api/anotherFunction' => ['FunctionName'],
  '/api/actionCall' => new Zaek\Framy\Action\Action,
  '/api/absolutePathFileCall' => '@/var/www/index.html',
]
```

## Config
|Name|Type|Default Value|Description|
|---|---|---|---|
|`homeDir`|string|`$_SERVER['DOCUMENT_ROOT']`|The root directory of web server|
|`routes`|array|-|List of routing rules will be applyed|
|`dataDir`|string|-|The directory where will be store all data (built-in file-based DB)|
|`useDefault`|bool|true|Use default framework settings (see below in section [default setup](#default-setup))|


## Default setup

The default functionality provided by Framy will place:
 
##### User with the following properties
```php
id = 10012019
login = 'framy'
email = 'framy@za-ek.ru'
```
##### Status=ok in JSON response
Every action has JSON response will be complete with {status:'ok'} flag

##### Routes to api
Built-in sign-up action is placed in ./bin directory. 
The `useDefault` option will add the route to such script.

## Built-in API

|URI|Method|Params|Return|
|---|---|---|---|
|/framy/signUp|POST|login<br />password<br />email|`{userId:(int)}`|