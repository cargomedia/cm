#!/usr/bin/env php
<?php

function includeIfExists($file) {
	return file_exists($file) && include $file;
}
if (!includeIfExists(__DIR__.'/../vendor/autoload.php') && !includeIfExists(__DIR__.'/../../../autoload.php')) {
	die('Please install project dependencies with `composer install`.' . PHP_EOL);
}

$bootloader = new CM_Bootloader(dirname(__DIR__) . '/');
$bootloader->load();

$manager = new CM_Cli_CommandManager();
$manager->autoloadCommands();
$returnCode = $manager->run(new CM_Cli_Arguments($argv));
exit($returnCode);
