<?php

class CM_Db_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @param string $namespace
	 */
	public function dbToFile($namespace) {
		$namespace = (string) $namespace;
		$tables = CM_Db_Db::exec("SHOW TABLES LIKE ?", array(strtolower($namespace) . '_%'))->fetchAllColumn();
		sort($tables);
		$dump = CM_Db_Db::getDump($tables, true);
		CM_File::create(CM_Util::getNamespacePath($namespace) . '/resources/db/structure.sql', $dump);
	}

	public function fileToDb() {
		CM_App::getInstance()->setupDatabase(true);
	}

	public function runUpdates() {
		$app = CM_App::getInstance();
		$output = $this->_getOutput();
		$output->writeln('Running database updates…');
		$app->runUpdateScripts(function ($version) use ($output) {
			$output->writeln('  Running update ' . $version . '…');
		});
		$app->setReleaseStamp();
	}

	/**
	 * @param integer $version
	 * @param string|null  $namespace
	 */
	public function runUpdate($version, $namespace = null) {
		$versionBumps = CM_App::getInstance()->runUpdateScript($namespace, $version);
		if ($versionBumps > 0) {
			$db = CM_Config::get()->CM_Db_Db->db;
			CM_Db_Db::exec('DROP DATABASE IF EXISTS `' . $db . '_test`');
		}
	}

	public static function getPackageName() {
		return 'db';
	}
}
