<?php

require_once dirname(__DIR__) . '/library/CM/Bootloader.php';
define('DIR_TESTS', __DIR__ . DIRECTORY_SEPARATOR);
define('DIR_TEST_DATA', DIR_TESTS . 'data' . DIRECTORY_SEPARATOR);

$bootloader = new CM_Bootloader(dirname(__DIR__) . '/', null);
$bootloader->setEnvironment('test');
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

!is_dir(DIR_TMP) ? mkdir(DIR_TMP) : null;
!is_dir(DIR_DATA) ? mkdir(DIR_DATA) : null;
!is_dir(DIR_USERFILES) ? mkdir(DIR_USERFILES) : null;

CMTest_TH::init();
