<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$bootloader = new CM_Bootloader_Testing(dirname(__DIR__) . '/', __DIR__ . '/');
$bootloader->setEnvironment('test');
$bootloader->load();

CMTest_TH::init();
