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

	public static function getPackageName() {
		return 'db';
	}
}