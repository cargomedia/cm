<?php

class CM_Debug {

  /** @var CM_Debug|null */
  private static $_instance = null;

  /** @var array */
  private $_stats = array();

  /** @var bool */
  private $_enabled;

  /**
   * @param bool $enabled
   */
  public function __construct($enabled) {
    $this->_enabled = (bool) $enabled;
  }

  /**
   * @param string          $key
   * @param string|string[] $value
   */
  public function incStats($key, $value) {
    if (!$this->_enabled) {
      return;
    }
    if (!array_key_exists($key, $this->_stats)) {
      $this->_stats[$key] = array();
    }
    $this->_stats[$key][] = $value;
  }

  /**
   * @return array[]
   */

  public function getStats() {
    return $this->_stats;
  }

  /**
   * @return CM_Debug
   */
  public static function getInstance() {
    if (self::$_instance === null) {
      self::$_instance = new self(CM_Bootloader::getInstance()->isDebug());
    }
    return self::$_instance;
  }
}
