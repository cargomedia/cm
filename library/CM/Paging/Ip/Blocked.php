<?php

class CM_Paging_Ip_Blocked extends CM_Paging_Ip_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('`ip`', 'cm_ipBlocked');
		$source->enableCacheLocal(60);
		parent::__construct($source);
	}

	/**
	 * @param int $ip
	 */
	public function add($ip) {
		$ip = (int) $ip;
		CM_Db_Db::replace('cm_ipBlocked', array('ip' => $ip, 'createStamp' => time()));
	}

	/**
	 * @param int $ip
	 */
	public function remove($ip) {
		$ip = (int) $ip;
		CM_Db_Db::delete('cm_ipBlocked', array('ip' => $ip));
	}

	/**
	 * @param int $age
	 */
	public static function deleteOlder($age) {
		$age = (int) $age;
		CM_Db_Db::delete('cm_ipBlocked', '`createStamp` < ' . (time() - $age));
	}
}
