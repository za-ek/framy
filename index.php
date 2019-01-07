<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $controller = new \Zaek\Controller([
        'homeDir' => __DIR__,
        'routes' => [
            'GET /' => function () {
                echo "Hello index!";
            }
        ]
    ]);
    $controller->handle();
    $controller->getResponse()->flush();

} catch (\Zaek\Routing\InvalidRoute $e) {
    echo $e->getMessage() . '<br/>';
}