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
		CM_Mysql::replace(TBL_CM_IPBLOCKED, array('ip' => (int) $ip, 'createStamp' => time()));
	}
	
	/**
	 * @param int $ip
	 */
	public function remove($ip) {
		CM_Mysql::delete(TBL_CM_IPBLOCKED, array('ip' => (int) $ip));
	}

	/**
	 * @param int $age
	 */
	public static function deleteOlder($age) {
		CM_Mysql::exec("DELETE FROM TBL_CM_IPBLOCKED WHERE `createStamp` < ?", time() - (int) $age);
	}
}
