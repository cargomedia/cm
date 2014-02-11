#!/usr/bin/env php
<?php

$dirVendor = null;
foreach (array(__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php') as $file) {
	if (file_exists($file)) {
		require $file;
		$dirVendor = realpath(dirname($file));
	}
}
if (!$dirVendor) {
	die('Please install project dependencies with `composer install`.' . PHP_EOL);
}

$bootloader = new CM_Bootloader(dirname($dirVendor) . '/');
$bootloader->load();

$manager = new CM_Cli_CommandManager();
$manager->autoloadCommands();
$returnCode = $manager->run(new CM_Cli_Arguments($argv));
exit($returnCode);
