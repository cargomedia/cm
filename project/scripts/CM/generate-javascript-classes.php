<?php

define("IS_CRON", true);
define('DIR_ROOT', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
require_once DIR_ROOT . 'library/CM/Bootloader.php';
CM_Bootloader::load(array('Autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$namespaces = array();
$sites = CM_Util::getClassChildren('CM_Site_Abstract');
foreach ($sites as $siteClassName) {
	$siteClass = new ReflectionClass($siteClassName);
	if (!$siteClass->isAbstract()) {
		/** @var $site CM_Site_Abstract */
		$site = new $siteClassName();
		$namespaces = array_merge($namespaces, $site->getNamespaces());
	}
}
$namespaces = array_unique($namespaces);

$skippedFiles = 0;
foreach (CM_View_Abstract::getClasses($namespaces) as $class) {
	$jsPath = preg_replace('/\.php$/', '.js', $class['path']);
	$className = $class['classNames'][0];
	if (file_exists($jsPath)) {
		$jsFile = new CM_File_JS($jsPath);
		if (!$jsFile->hasClassDeclaration($className)) {
			$jsFile = CM_File_JS::createLibraryClass($className);
			echo ' merge  ' . $jsFile->getPath() . PHP_EOL;
		} else {
			$skippedFiles++;
		}
	} else {
		$jsFile = CM_File_JS::createLibraryClass($className);
		echo 'create  ' . $jsFile->getPath() . PHP_EOL;
	}
}
echo '  skip  [' . $skippedFiles . '] files' . PHP_EOL;