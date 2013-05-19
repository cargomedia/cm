<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
define('DIR_TESTS', __DIR__ . DIRECTORY_SEPARATOR);
define('DIR_TEST_DATA', DIR_TESTS . 'data' . DIRECTORY_SEPARATOR);

$bootloader = new CM_Bootloader(dirname(__DIR__) . '/', null);
$bootloader->setEnvironment('test');
$bootloader->load(array('constants', 'exceptionHandler', 'errorHandler', 'defaults'));

CMTest_TH::init();
