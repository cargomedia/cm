#!/usr/bin/env php
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
$bootloader = new CM_Bootloader(dirname(__DIR__) . '/');
$bootloader->load();

$manager = new CM_Cli_CommandManager();
$returnCode = $manager->run(new CM_Cli_Arguments($argv));
exit($returnCode);
