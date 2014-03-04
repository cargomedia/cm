<?php

class CM_Paging_Action_Ip extends CM_Paging_Action_Abstract {

  /** @var int */
  private $_ip;

  /**
   * @param int      $ip
   * @param int|null $actionType
   * @param int|null $actionVerb
   * @param int|null $period
   * @param int|null $upperBound
   */
  public function __construct($ip, $actionType = null, $actionVerb = null, $period = null, $upperBound = null) {
    $cacheEnabled = false;
    $this->_ip = (int) $ip;
    $period = (int) $period;
    $where = 'ip=' . $this->_ip . ' AND `actionLimitType` IS NULL';
    if ($actionType) {
      $actionType = (int) $actionType;
      $where .= ' AND `type` = ' . $actionType;
    }
    if ($actionVerb) {
      $actionVerb = (int) $actionVerb;
      $where .= ' AND `verb` = ' . $actionVerb;
    }
    if ($period) {
      if (null !== $upperBound) {
        $upperBound = (int) $upperBound;
        $cacheEnabled = true;
      } else {
        $upperBound = time();
      }
      $lowerBound = $upperBound - $period;
      $where .= ' AND `createStamp` > ' . $lowerBound . ' AND `createStamp` <= ' . $upperBound;
    }
    $source = new CM_PagingSource_Sql_Deferred('type, verb, createStamp', 'cm_action', $where, '`createStamp` DESC');
    if ($cacheEnabled) {
      $source->enableCacheLocal();
    }
    parent::__construct($source);
  }

  public function add(CM_Action_Abstract $action) {
    CM_Db_Db::insertDelayed('cm_action',
      array('ip' => $this->_ip, 'verb' => $action->getVerb(), 'type' => $action->getType(), 'createStamp' => time()));
  }
}
