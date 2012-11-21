<?php

define("IS_CRON", true);

require_once dirname(__DIR__) . '/library/CM/Bootloader.php';
$bootloader = new CM_Bootloader(dirname(dirname(__DIR__)) . '/', null);
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$viewClasses = CM_View_Abstract::getClasses(CM_Bootloader::getInstance()->getNamespaces(), CM_View_Abstract::CONTEXT_JAVASCRIPT);
foreach ($viewClasses as $path => $className) {
	$jsPath = preg_replace('/\.php$/', '.js', $path);
	if (!file_exists($jsPath)) {
		$jsFile = CM_File_Javascript::createLibraryClass($className);
		echo 'create  ' . $jsFile->getPath() . PHP_EOL;
	}
}