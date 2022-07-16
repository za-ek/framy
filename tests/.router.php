<?php

use Zaek\Framy\App;

function test() : void {
    echo "Test\n";
}
$class = new class {
    public function methodB() : void {
        echo "Method B\n";
    }
};

return [
    'CLI /cb' => function(App $app) {
        echo "Callable\n";
    },
    'CLI /file' => '/Cli/Index.php',
    'CLI /f' => ['test'],
    'CLI /m' => [$class, 'methodB'],
];