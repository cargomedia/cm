<?php

class CM_Mysql_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @param string|null $tablePrefix
	 */
	public function dump($tablePrefix = null) {
		$tablePrefix = (string) $tablePrefix;
		$tables = CM_Mysql::exec("SHOW TABLES LIKE '?'", $tablePrefix . '%')->fetchCol();
		sort($tables);
		$dump = CM_Mysql::getDump($tables, true);
		CM_File::create(CM_Util::getNamespacePath('CM') . '/resources/db/structure.sql', $dump);
	}

	public static function getPackageName() {
		return 'db';
	}
}