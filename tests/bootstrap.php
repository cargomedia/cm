<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
$rootPath = dirname(__DIR__) . '/';

$application = new CM_App_Testing($rootPath);
$application->installGlobalHandlers();
$application->bootstrap();

$suite = new CMTest_TestSuite();
$suite->setDirTestData(__DIR__ . '/data/');
$suite->bootstrap();
