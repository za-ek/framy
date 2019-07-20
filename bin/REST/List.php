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
    $list = $tbl->select();
    return [
        'list' => $list->toAssoc()
    ];
} catch (\Throwable $e) {
    throw $e;
}