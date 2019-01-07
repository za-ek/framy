<?php
function test() {
    echo "Test\n";
}
class testA {
    public function methodB() {
        echo "Method B\n";
    }
}

return [
    'CLI /cb' => function(\Zaek\Application $app) {
        echo "Callable\n";
    },
    'CLI /file' => '/Cli/Index.php',
    'CLI /f' => ['test'],
    'CLI /m' => ['testA', 'methodB'],
];