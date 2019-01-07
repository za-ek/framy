<?php
namespace Zaek\Framy;

interface Action
{
    public function execute(Application $app);
}