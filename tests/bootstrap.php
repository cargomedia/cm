<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
define('DIR_TESTS', __DIR__ . DIRECTORY_SEPARATOR);
define('DIR_TEST_DATA', DIR_TESTS . 'data' . DIRECTORY_SEPARATOR);
define('DIR_LIBRARY_TESTS', 'tests/library/');

$bootloader = new CMTest_Bootloader(dirname(__DIR__) . '/', '../');
$bootloader->setEnvironment('test');
$bootloader->load(array('constants', 'exceptionHandler', 'errorHandler', 'defaults'));

!is_dir(DIR_TMP) ? mkdir(DIR_TMP) : null;
!is_dir(DIR_DATA) ? mkdir(DIR_DATA) : null;
!is_dir(DIR_USERFILES) ? mkdir(DIR_USERFILES) : null;

CMTest_TH::init();
