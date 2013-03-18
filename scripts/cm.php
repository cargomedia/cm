#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/library/CM/Bootloader.php';
$bootloader->setEnvironment('cli');
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$manager = new CM_Cli_CommandManager();
$returnCode = $manager->run(new CM_Cli_Arguments($argv));
exit($returnCode);
