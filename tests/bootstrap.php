<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
$rootPath = dirname(__DIR__) . '/';

$bootloader = new CM_Bootloader_Testing($rootPath);
$bootloader->load();

$application = new CM_App($rootPath);
$application->bootstrap();

$suite = new CMTest_TestSuite();
$suite->setDirTestData(__DIR__ . '/data/');
$suite->bootstrap();
