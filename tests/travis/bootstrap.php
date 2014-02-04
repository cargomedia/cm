<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
define('DIR_TESTS', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('DIR_TEST_DATA', DIR_TESTS . 'data' . DIRECTORY_SEPARATOR);

$bootloader = new CM_Bootloader(dirname(dirname(__DIR__)) . '/', null);
$bootloader->setEnvironment(array('test', 'travis'));
$bootloader->load(array('errorHandler', 'constants', 'exceptionHandler', 'defaults'));

CMTest_TH::init();
