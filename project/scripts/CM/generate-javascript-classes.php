<?php

define("IS_CRON", true);
define('DIR_ROOT', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
require_once DIR_ROOT . 'library/CM/Bootloader.php';
CM_Bootloader::load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$namespaces = CM_Util::getNamespaces();
$viewClasses = CM_View_Abstract::getClasses($namespaces, CM_View_Abstract::CONTEXT_JAVASCRIPT);
foreach ($viewClasses as $path => $className) {
	$jsPath = preg_replace('/\.php$/', '.js', $path);
	if (!file_exists($jsPath)) {
		$jsFile = CM_File_Javascript::createLibraryClass($className);
		echo 'create  ' . $jsFile->getPath() . PHP_EOL;
	}
}