<?php

class CM_Paging_Ip_Blocked extends CM_Paging_Ip_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('`ip`', TBL_CM_IPBLOCKED);
		$source->enableCacheLocal(60);
		parent::__construct($source);
	}

	/**
	 * @param int $ip
	 */
	public function add($ip) {
		$ip = (int) $ip;
		CM_Mysql::replace(TBL_CM_IPBLOCKED, array('ip' => $ip, 'createStamp' => time()));
	}

	/**
	 * @param int $ip
	 */
	public function remove($ip) {
		$ip = (int) $ip;
		CM_Mysql::delete(TBL_CM_IPBLOCKED, array('ip' => $ip));
	}

	/**
	 * @param int $age
	 */
	public static function deleteOlder($age) {
		$age = (int) $age;
		CM_Db_Db::delete(TBL_CM_IPBLOCKED, '`createStamp` < ' . (time() - $age));
	}
}
