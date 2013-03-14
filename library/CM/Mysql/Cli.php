<?php

class CM_Mysql_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @param string $namespace
	 */
	public function dump($namespace) {
		$namespace = (string) $namespace;
		$tables = CM_Mysql::exec("SHOW TABLES LIKE '?'", strtolower($namespace) . '_%')->fetchCol();
		sort($tables);
		$dump = CM_Db_Db::getDump($tables, true);
		CM_File::create(CM_Util::getNamespacePath($namespace) . '/resources/db/structure.sql', $dump);
	}

	public function runUpdates() {
		$app = CM_App::getInstance();
		$output = $this->_getOutput();
		$versionBumps = $app->runUpdateScripts(function ($version) use ($output) {
			$output->writeln('Running update ' . $version . '...');
		});
		if ($versionBumps > 0) {
			$db = CM_Config::get()->CM_Mysql->db;
			CM_Mysql::exec('DROP DATABASE IF EXISTS `' . $db . '_test`');
		}
		$app->setReleaseStamp();
	}

	/**
	 * @param integer $version
	 * @param string|null  $namespace
	 */
	public function runUpdate($version, $namespace = null) {
		$versionBumps = CM_App::getInstance()->runUpdateScript($namespace, $version);
		if ($versionBumps > 0) {
			$db = CM_Config::get()->CM_Mysql->db;
			CM_Mysql::exec('DROP DATABASE IF EXISTS `' . $db . '_test`');
		}
	}

	public static function getPackageName() {
		return 'db';
	}
}
