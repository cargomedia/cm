<?php

define("IS_CRON", true);
define('DIR_ROOT', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
require_once DIR_ROOT . 'library/CM/Bootloader.php';
CM_Bootloader::load(array('Autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

try {
	$path = DIR_ROOT . 'config' . DIRECTORY_SEPARATOR . 'class-types.php';
	CM_File::create($path, CM_App::getInstance()->generateClassTypesConfig());
	echo 'Class types config file has been created at `' . $path . '`' . PHP_EOL;
} catch (Exception $e) {
	echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
