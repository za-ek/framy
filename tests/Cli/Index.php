<?php
/**
 * @var $this \Zaek\Framy\Application
 */

use Zaek\Framy\Response\Json;

$this->getController()->setResponse(new Json());

echo "Hello index!";

return [
    'index' => true
];