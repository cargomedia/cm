<?php

define("IS_CRON", true);
require_once dirname(dirname(__DIR__)) . '/library/CM/Bootloader.php';
$bootloader = new CM_Bootloader(dirname(dirname(__DIR__)) . '/', null);
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$jobManager = new CM_JobManager();
$jobManager->start();