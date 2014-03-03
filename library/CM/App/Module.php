<?php

class CM_App_Module {

  /** @var string */
  private $_name;

  /** @var string */
  private $_path;

  /**
   * @param string $name
   * @param string $path
   */
  public function __construct($name, $path) {
    $this->_name = (string) $name;
    $this->_path = (string) $path;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * @return string
   */
  public function getPath() {
    return $this->_path;
  }
}
