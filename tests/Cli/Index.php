<?php
/**
 * @var $this \Zaek\Application
 */
$this->getController()->setResponse(new \Zaek\Response\Json());

echo "Hello index!";

return [
    'index' => true
];