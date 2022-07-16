<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $app = new \Zaek\Framy\App([
        'homeDir' => __DIR__,
        'routes' => [
            'GET /' => function () {
                echo "Hello index!";
            },
            'REST /users' => '@' . __DIR__ . '/bin/REST'
        ],
        'tempDir' => sys_get_temp_dir() . '/z_framy',
        'dataDir' => __DIR__.'/db'
    ]);
    $app->handle();
    $app->response()->flush();

} catch (\Zaek\Framy\Routing\InvalidRoute $e) {
    echo $e->getMessage() . '<br/>';
}