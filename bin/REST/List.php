<?php
/**
 * @var $this \Zaek\Framy\App
 */

$arr = explode('/', $this->request()->getPath());
$tbl = array_pop($arr);
try {
    $tbl = $this->db()->table($tbl);
    $tbl->open();
    $tbl->read();
    $list = $tbl->select();
    return [
        'list' => $list->toAssoc()
    ];
} catch (\Throwable $e) {
    throw $e;
}