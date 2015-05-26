<?php

abstract class CM_Paging_Log_Abstract extends CM_Paging_Abstract implements CM_Typed {

    /**
     * @param string       $msg
     * @param mixed[]|null $metaInfo
     */
    public function add($msg, array $metaInfo = null) {
        $metaInfo = array_merge((array) $metaInfo, $this->_getDefaultMetaInfo());
        $this->_add($msg, $metaInfo);
    }

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
        $source = new CM_PagingSource_Sql_Deferred($select, 'cm_log', $where, $order, null, $group);
        parent::__construct($source);
    }

    public function flush() {
        CM_Db_Db::delete('cm_log', array('type' => $this->getType()));
    }

    public function cleanUp() {
        $this->_deleteOlderThan(7 * 86400);
    }

    /**
     * @param int $age
     */
    protected function _deleteOlderThan($age) {
        $age = (int) $age;
        $type = $this->getType();
        $deleteOlderThan = time() - $age;
        CM_Db_Db::exec('DELETE FROM `cm_log` WHERE `timestamp` <= ? AND `type` = ?', array($deleteOlderThan, $type));
        $this->_change();
    }

    /**
     * @param int       $type
     * @param bool|null $aggregate
     * @param int|null  $ageMax
     * @return CM_Paging_Log_Abstract
     */
    final public static function factory($type, $aggregate = null, $ageMax = null) {
        $className = self::_getClassName($type);
        return new $className($aggregate, $ageMax);
    }

    /**
     * @return array
     */
    protected function _getDefaultMetaInfo() {
        $metaInfo = array();
        if ($fqdn = CM_Util::getFqdn()) {
            $metaInfo['fqdn'] = $fqdn;
        }
        if (CM_Http_Request_Abstract::hasInstance()) {
            $request = CM_Http_Request_Abstract::getInstance();
            $metaInfo['uri'] = $request->getUri();
            if ($viewer = $request->getViewer()) {
                $metaInfo['userId'] = $viewer->getId();
            }
            if ($ip = $request->getIp()) {
                $metaInfo['ip'] = $ip;
            }
            if ($request->hasHeader('Referer')) {
                $metaInfo['referer'] = $request->getHeader('Referer');
            }
            if ($request->hasHeader('User-Agent')) {
                $metaInfo['useragent'] = $request->getHeader('User-Agent');
            }
        }
        return $metaInfo;
    }

    /**
     * @param string       $msg
     * @param mixed[]|null $metaInfo
     */
    protected function _add($msg, array $metaInfo = null) {
        $msg = (string) $msg;
        $values = array('type' => $this->getType(), 'msg' => $msg, 'timeStamp' => time());
        if (!empty($metaInfo)) {
            $values['metaInfo'] = $this->_serialize($metaInfo);
        }
        CM_Db_Db::insertDelayed('cm_log', $values);
    }

    protected function _processItem($item) {
        if (!empty($item['metaInfo'])) {
            $metaInfo = $this->_unserialize($item['metaInfo'], true);
            $item['metaInfo'] = $metaInfo;
        }
        return $item;
    }

    /**
     * @param mixed[] $valueList
     * @return string
     */
    protected function _serialize(array $valueList) {
        $variableInspector = new CM_Debug_VariableInspector();
        $valueListString = Functional\map($valueList, function ($value) use($variableInspector) {
            return $variableInspector->getDebugInfo($value, ['recursive' => true]);
        });
        return serialize($valueListString);
    }

    /**
     * @param string $value
     * @return string[]|null
     */
    protected function _unserialize($value) {
        $result = @unserialize($value);
        if (!is_array($result)) {
            return null;
        }
        return $result;
    }
}
