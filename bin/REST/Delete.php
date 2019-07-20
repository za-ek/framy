<?php
/**
 * @var $this \Zaek\Framy\Application
 */

$arr = explode('/', $this->getAction()->getRequest()->getPath());
$tbl = array_pop($arr);
try {
    $tbl = $this->getController()->db()->table($tbl);
    $tbl->open();
    $tbl->read();
    $result = $tbl->delete([

    ]);
    $tbl->save();
    $tbl->close();

    return $result;
} catch (\Throwable $e) {
    throw $e;
}