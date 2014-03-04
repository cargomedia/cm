<?php

class CM_Clockwork_Event {

  /** @var DateInterval */
  private $_interval;

  /** @var DateTime */
  private $_nextRun;

  /** @var callable[] */
  private $_callbacks;

  /**
   * @param DateInterval $interval
   */
  public function __construct(DateInterval $interval) {
    $this->_interval = $interval;
    $this->_callbacks = array();
  }

  /**
   * @return bool
   */
  public function shouldRun() {
    return $this->_getCurrentDateTime() >= $this->_nextRun;
  }

  /**
   * @param callable $callback
   * @throws CM_Exception_Invalid
   */
  public function registerCallback($callback) {
    if (!is_callable($callback)) {
      throw new CM_Exception_Invalid('$callback needs to be callable');
    }
    $this->_callbacks[] = $callback;
  }

  public function run() {
    foreach ($this->_callbacks as $callback) {
      call_user_func($callback);
    }
    $nextRun = $this->_getCurrentDateTime();
    $nextRun->add($this->_interval);
    $this->_nextRun = $nextRun;
  }

  /**
   * @return DateTime
   */
  protected function _getCurrentDateTime() {
    return new DateTime();
  }
}
