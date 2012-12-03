<?php

class CM_Mysql_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @param string $namespace
	 */
	public function dump($namespace) {
		$namespace = (string) $namespace;
		$tables = CM_Mysql::exec("SHOW TABLES LIKE '?'", strtolower($namespace) . '_%')->fetchCol();
		sort($tables);
		$dump = CM_Mysql::getDump($tables, true);
		CM_File::create(CM_Util::getNamespacePath($namespace) . '/resources/db/structure.sql', $dump);
	}

	public function runUpdateScripts() {
		$app = CM_App::getInstance();
		$versionBumps = $app->runUpdateScripts(function ($version) {
			echo 'Running update ' . $version . '...' . PHP_EOL;
		});
		if ($versionBumps > 0) {
			CM_Mysql::exec('DROP DATABASE IF EXISTS `skadate_test`');
		}
		$app->setReleaseStamp();
	}

	/**
	 * @param integer $version
	 * @param string|null  $namespace
	 */
	public function runScript($version, $namespace = null) {
		CM_App::getInstance()->runUpdateScript($namespace, $version);
		CM_Mysql::exec('DROP DATABASE IF EXISTS `skadate_test`');

	}

	public static function getPackageName() {
		return 'db';
	}
}