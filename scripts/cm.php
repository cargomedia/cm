#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
$bootloader = new CM_Bootloader(dirname(__DIR__) . '/', null);
$bootloader->setEnvironment('cli');
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$manager = new CM_Cli_CommandManager();
$returnCode = $manager->run(new CM_Cli_Arguments($argv));
exit($returnCode);
