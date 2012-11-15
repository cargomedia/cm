#!/usr/bin/env php
<?php

define("IS_CRON", true);
define('DIR_ROOT', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
require_once DIR_ROOT . 'library/CM/Bootloader.php';
CM_Bootloader::load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$jobManager = new CM_JobManager();
$jobManager->start();