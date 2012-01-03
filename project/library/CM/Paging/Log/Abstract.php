<?php

abstract class CM_Paging_Log_Abstract extends CM_Paging_Abstract {

	/**
	 * @param boolean $aggregate
	 * @param int     $ageMax
	 */
	public function __construct($aggregate = false, $ageMax = null) {
		$select = '`id`, `msg`, `timeStamp`, `metaInfo`';
		$where = '`type` = ' . $this->getType();
		$order = '`timeStamp` DESC';
		$group = null;
		if ($ageMax) {
			$where .= ' AND `timeStamp` > ' . (time() - (int) $ageMax);
		}
		if ($aggregate) {
			$select = '`msg`, COUNT(*) AS `count`';
			$group = '`msg`';
			$order = '`count` DESC';
		}
		$source = new CM_PagingSource_Sql_Deferred($select, TBL_CM_LOG, $where, $order, null, $group);
		parent::__construct($source);
	}

	/**
	 * @param int $age
	 */
	public static function deleteOlder($age) {
		$age = (int) $age;
		CM_Mysql::exec("DELETE FROM TBL_CM_LOG WHERE `timeStamp` < ?", time() - $age);
	}

	/**
	 * @param int $type
	 * @return CM_Paging_Log_Abstract
	 */
	final public static function factory($type) {
		$className = self::_getClassName($type);
		return new $className();
	}

	/**
	 * @param string $msg
	 * @param array  $metaInfo
	 */
	protected function _add($msg, array $metaInfo = null) {
		$msg = (string) $msg;
		$values = array('type' => $this->getType(), 'msg' => $msg, 'timeStamp' => time());
		if ($metaInfo) {
			$values['metaInfo'] = serialize($metaInfo);
		}
		CM_Mysql::insertDelayed(TBL_CM_LOG, $values);
	}

	protected function _processItem($item) {
		if (!empty($item['metaInfo'])) {
			$item['metaInfo'] = unserialize($item['metaInfo']);
		}
		return $item;
	}
}
