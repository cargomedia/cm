<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
define('DIR_TESTS', __DIR__ . DIRECTORY_SEPARATOR);
define('DIR_TEST_DATA', DIR_TESTS . 'data' . DIRECTORY_SEPARATOR);

$bootloader = new CM_Bootloader_Testing(dirname(__DIR__) . '/');
$bootloader->setEnvironment('test');
$bootloader->load(array('errorHandler', 'constants', 'exceptionHandler', 'defaults'));

CMTest_TH::init();
