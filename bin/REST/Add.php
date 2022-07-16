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
    $result = $tbl->insert($this->request()->post('data')['data']);
    $tbl->save();
    $tbl->close();

    return $result;
} catch (\Throwable $e) {
    throw $e;
}