# Framy
Simple php framework will be useful for creating provisional endpoints in frontend development.

## Start

Create entrypoint for you scripts and define configuration as mentioned in the [next part](#config)
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $controller = new \Zaek\Framy\Controller([
        'homeDir' => __DIR__,
        'routes' => [
            'GET /' => '/web/Index.php',
        ]
    ]);
    $controller->handle();
    $controller->getResponse()->flush();

} catch (\Exception $e) {
    echo $e->getMessage();
}
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