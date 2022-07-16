<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $application = new \Zaek\Framy\Application([
        'homeDir' => __DIR__,
        'dataDir' => __DIR__ . '/../db',
        'routes' => include '.router.php'
    ]);
    $application->handle();
    $application->response()->flush();

} catch (\Zaek\Framy\Routing\InvalidRoute $e) {
    echo $e->getMessage() . '<br/>';
} catch (\Zaek\Framy\Request\InvalidRequest $e) {
    echo $e->getMessage() . '<br/>';
}