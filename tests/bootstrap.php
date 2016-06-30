<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', dirname(__DIR__) . '/vendor/autoload.php');
}

$bootloader = new CM_Bootloader_Testing(dirname(__DIR__) . '/');
$bootloader->load();

$suite = new CMTest_TestSuite();
$suite->setDirTestData(__DIR__ . '/data/');
$suite->bootstrap();
