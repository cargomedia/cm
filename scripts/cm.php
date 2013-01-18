#!/usr/bin/env php
<?php

define('IS_CRON', true);
require_once dirname(__DIR__) . '/library/CM/Bootloader.php';
$bootloader = new CM_Bootloader(dirname(__DIR__) . '/', null);
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));
CM_Config::loadConfig('cli.php');

$manager = new CM_Cli_CommandManager();
$returnCode = $manager->run(new CM_Cli_Arguments($argv));
exit($returnCode);
