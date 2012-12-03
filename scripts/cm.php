#!/usr/bin/env php
<?php

define('IS_CRON', true);
require_once dirname(__DIR__) . '/library/CM/Bootloader.php';
$bootloader = new CM_Bootloader(dirname(__DIR__) . '/', null);
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$manager = new CM_Cli_CommandManager();
$arguments = new CM_Cli_Arguments($argv);
echo $manager->run($arguments);