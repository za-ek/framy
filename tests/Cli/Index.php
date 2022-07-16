<?php
/**
 * @var $this \Zaek\Framy\App
 */

use Zaek\Framy\Response\Json;

$this->setResponse(new Json());

echo "Hello index!";

return [
    'index' => true
];