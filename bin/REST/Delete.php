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
    $result = $tbl->delete([

    ]);
    $tbl->save();
    $tbl->close();

    return $result;
} catch (\Throwable $e) {
    throw $e;
}