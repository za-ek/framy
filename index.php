<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $controller = new \Zaek\Framy\Controller([
        'homeDir' => __DIR__,
        'routes' => [
            'GET /' => function () {
                echo "Hello index!";
            }
        ],
        'tempDir' => sys_get_temp_dir() . '/z_framy',
        'dataDir' => __DIR__.'/db'
    ]);
    $controller->handle();
    $controller->getResponse()->flush();

} catch (\Zaek\Framy\Routing\InvalidRoute $e) {
    echo $e->getMessage() . '<br/>';
}