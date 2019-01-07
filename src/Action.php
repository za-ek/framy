<?php
namespace Zaek;

interface Action
{
    public function execute(Application $app);
}