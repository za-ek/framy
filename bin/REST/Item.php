<?php
/**
 * @var $this \Zaek\Framy\App
 */

$arr = explode('/', $this->request()->getPath());
$id = array_pop($arr);
$tbl = array_pop($arr);
try {
    $tbl = $this->db()->table($tbl);
    $tbl->open();
    $tbl->read();
    $list = $tbl->select(['_id' => intval($id)]);
    return [
        'item' => $list->fetch($list::FETCH_ASSOC)
    ];
} catch (\Throwable $e) {
    throw $e;
}