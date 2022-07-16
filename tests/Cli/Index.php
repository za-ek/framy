<?php
/**
 * @var $this \Zaek\Framy\Application
 */

use Zaek\Framy\Response\Json;

$this->setResponse(new Json());

echo "Hello index!";

return [
    'index' => true
];