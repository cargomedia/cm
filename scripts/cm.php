#!/usr/bin/env php
<?php

define("IS_CRON", true);
define('DIR_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
require_once DIR_ROOT . 'library/CM/Bootloader.php';
CM_Bootloader::load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$manager = new CM_Cli_CommandManager();
$manager->addScannedDir(DIR_LIBRARY);
$arguments = new CM_Cli_Arguments($argv);
echo $manager->run($arguments);