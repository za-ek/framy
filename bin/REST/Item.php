<?php
/**
 * @var $this \Zaek\Framy\Application
 */

$arr = explode('/', $this->getAction()->getUri());
$id = array_pop($arr);
$tbl = array_pop($arr);
try {
    $tbl = $this->getController()->db()->table($tbl);
    $tbl->open();
    $tbl->read();
    $list = $tbl->select(['_id' => intval($id)]);
    return [
        'item' => $list->fetch($list::FETCH_ASSOC)
    ];
} catch (\Throwable $e) {
    throw $e;
}