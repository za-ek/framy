<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $controller = new \Zaek\Framy\Controller([
        'homeDir' => __DIR__,
        'dataDir' => __DIR__ . '/../db',
        'routes' => include '.router.php'
    ]);
    $controller->handle();
    $controller->getResponse()->flush();

} catch (\Zaek\Framy\Routing\InvalidRoute $e) {
    echo $e->getMessage() . '<br/>';
} catch (\Zaek\Framy\Request\InvalidRequest $e) {
    echo $e->getMessage() . '<br/>';
}