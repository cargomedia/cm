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

    public static function deleteOld() {
        CM_Db_Db::delete('cm_ipBlocked', '`createStamp` < ' . (time() - self::_getMaxAge()));
    }

    /**
     * @return int
     */
    protected static function _getMaxAge() {
        return (int) self::_getConfig()->maxAge;
    }
}
